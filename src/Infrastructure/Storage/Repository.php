<?php

namespace App\Infrastructure\Storage;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\Snapshot;
use App\Infrastructure\EventSource\SnapshotRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use ReflectionClass;
use ReflectionException;

use function method_exists;

abstract class Repository
{
    /**
     * AggregateRoot already loaded to avoid doctrine load from query cache the wrong version.
     * @var ArrayCollection|AggregateRoot[]
     */
    private $loaded;

    /**
     * @var array [property => class]
     */
    protected $dependencies = [];

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EventSourceRepositoryInterface
     */
    protected $eventSource;

    /**
     * @var SnapshotRepositoryInterface
     */
    private $snapshot;

    public function __construct(
        EntityManagerInterface $em,
        EventSourceRepositoryInterface $eventSource,
        SnapshotRepositoryInterface $snapshot
    ) {
        $this->em = $em;
        $this->eventSource = $eventSource;
        $this->snapshot = $snapshot;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return mixed object
     * @throws ReflectionException
     */
    protected function load(string $type, string $id)
    {
        if (isset($this->loaded[$id])) {
            return $this->loaded[$id];
        }

        $snapshot = $this->snapshot->load($id, $type);

        if ($snapshot) {
            $object = $this->deserializeFromSnapshot($snapshot->getData());
        } else {
            $object = new $type($id);
        }

        $changes = $this->eventSource->findEvents($id, $type, $object->getVersion());
        $this->overloadDependencies($changes);

        $object->replay($changes);

        $object = $this->em->merge($object);
        $this->loaded[$id] = $object;

        return $object;
    }

    protected function deserializeFromSnapshot($object)
    {
        return $object;
    }

    /**
     * Loop over the changes list and load the dependencies from the database. Dependencies must be not null and
     * must have and $id.
     *
     * @param array $changes
     *
     * @return $this
     * @throws ReflectionException
     */
    protected function overloadDependencies(array $changes): self
    {
        /** @var Changed $change */
        foreach ($changes as $change) {
            $payload = $change->getPayload();

            foreach ($this->dependencies as $property => $class) {
                if (property_exists($payload, $property)) {
                    $reflectionClass = new ReflectionClass(get_class($payload));
                    $reflectionProperty = $reflectionClass->getProperty($property);
                    $reflectionProperty->setAccessible(true);

                    $value = $reflectionProperty->getValue($payload);
                    if ($value) {
                        try {
                            $value = $this->em->find($class, $value);
                        } catch (ORMInvalidArgumentException $e) {
                            continue;
                        }

                        $reflectionProperty->setValue($payload, $value);
                    }
                }
            }
        }

        return $this;
    }

    protected function store(AggregateRoot $object)
    {
        $changes = $object->getChanges();
        if ($changes && !$changes->isEmpty()) {
            $this->em->persist($object);
            $this->em->flush();

            $this->unburdenDependencies($changes);
            $this->eventSource->saveEvents($changes);
            $this->em->flush();

            if (!($object->getVersion() % 5)) {
                $this->snapshot->save(
                    (new Snapshot($object->getId()))
                        ->setType(\get_class($object))
                        ->setVersion($object->getVersion())
                        ->setData($this->serializeToSnapshot($object))
                );
            }
        }

        $this->loaded[$object->getId()] = $object;
    }

    protected function unburdenDependencies(ArrayCollection $changes): self
    {
        /** @var Changed $change */
        foreach ($changes as $change) {
            $payload = $change->getPayload();

            foreach ($this->dependencies as $property => $class) {
                if (property_exists($payload, $property)) {
                    $reflectionClass = new ReflectionClass(get_class($payload));
                    $reflectionProperty = $reflectionClass->getProperty($property);
                    $reflectionProperty->setAccessible(true);

                    $value = $reflectionProperty->getValue($payload);
                    if ($value && method_exists($value, 'getId')) {
                        $value = new $class($value->getId());

                        $reflectionProperty->setValue($payload, $value);
                    }
                }
            }
        }

        return $this;
    }

    protected function serializeToSnapshot($object)
    {
        return $object;
    }
}
