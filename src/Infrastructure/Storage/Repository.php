<?php

namespace App\Infrastructure\Storage;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\Snapshot;
use App\Infrastructure\EventSource\SnapshotRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use ReflectionClass;
use ReflectionException;

use function array_merge;
use function method_exists;

abstract class Repository
{
    /**
     * AggregateRoot already loaded to avoid doctrine load from query cache the wrong version.
     * @var ArrayCollection|AggregateRoot[]
     */
    private $loaded;

    /**
     * Properties have to be unburden before store them in the event source table.
     *
     * @var array [property => class]
     */
    protected $dependencies = [];

    /**
     * Properties have to be unburden during serialize for snapshot.
     *
     * @var array [property => class]
     */
    protected $serializeDependencies = [];

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
        $this->overloadChangesDependencies($changes);

        $object->replay($changes);

        $object = $this->em->merge($object);
        $this->loaded[$id] = $object;

        return $object;
    }

    protected function deserializeFromSnapshot($object)
    {
        $serialize = clone $object;

        $this->overloadDependencies($serialize, $this->serializeDependencies);

        return $serialize;
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
    protected function overloadChangesDependencies(array $changes): self
    {
        /** @var Changed $change */
        foreach ($changes as $change) {
            $payload = $change->getPayload();

            $this->overloadDependencies($payload, $this->dependencies);
        }

        return $this;
    }

    /**
     * @param $object
     * @param array $dependencies [property => class]
     *
     * @return $this
     * @throws ReflectionException
     */
    private function overloadDependencies($object, array $dependencies): self
    {
        if (empty($dependencies)) {
            return $this;
        }

        $reflect = new ReflectionClass(get_class($object));
        // This gets the real object, the one that the Doctrine\Common\Persistence\Proxy extends
        if ($object instanceof Proxy) {
            $reflect = $reflect->getParentClass();
        }

        foreach ($dependencies as $property => $class) {
            $reflectionProperty = null;
            if ($reflect->hasProperty($property)) {
                $reflectionProperty = $reflect->getProperty($property);
            } elseif ($reflect->getParentClass() && $reflect->getParentClass()->hasProperty($property)) {
                $reflectionProperty = $reflect->getParentClass()->getProperty($property);
            }

            if($reflectionProperty) {
                $reflectionProperty->setAccessible(true);

                $value = $reflectionProperty->getValue($object);
                if ($value && !$value instanceof ArrayCollection) {
                    try {
                        $value = $this->em->find($class, $value);
                        $reflectionProperty->setValue($object, $value);
                    } catch (ORMInvalidArgumentException $e) {
                        continue;
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

            $this->unburdenChangesDependencies($changes);
            $this->eventSource->saveEvents($changes);
            $this->em->flush();

            if (!($object->getVersion() % 5)) {
                $this->snapshot->save(
                    (new Snapshot($object->getId()))
                        ->setType($object->getAggregateType())
                        ->setVersion($object->getVersion())
                        ->setData($this->serializeToSnapshot($object))
                );
            }
        }

        $this->loaded[$object->getId()] = $object;
    }

    protected function unburdenChangesDependencies(ArrayCollection $changes): self
    {
        /** @var Changed $change */
        foreach ($changes as $change) {
            $payload = $change->getPayload();

            $this->unburdenDependencies($payload, $this->dependencies);
        }

        return $this;
    }

    /**
     * @param $object
     * @param array $dependencies [property => class]
     *
     * @return $this
     * @throws ReflectionException
     */
    private function unburdenDependencies($object, array $dependencies): self
    {
        if (empty($dependencies)) {
            return $this;
        }

        $reflect = new ReflectionClass(get_class($object));
        // This gets the real object, the one that the Proxy extends
        if ($object instanceof Proxy) {
            $reflect = $reflect->getParentClass();
        }

        foreach ($dependencies as $property => $class) {
            $reflectionProperty = null;
            if ($reflect->hasProperty($property)) {
                $reflectionProperty = $reflect->getProperty($property);
            } elseif ($reflect->getParentClass() && $reflect->getParentClass()->hasProperty($property)) {
                $reflectionProperty = $reflect->getParentClass()->getProperty($property);
            }

            if($reflectionProperty) {
                $reflectionProperty->setAccessible(true);

                $value = $reflectionProperty->getValue($object);
                if ($value && method_exists($value, 'getId')) {
                    $value = new $class($value->getId());
                } elseif ($value instanceof ArrayCollection) {
                    $value = new $class();
                }

                $reflectionProperty->setValue($object, $value);
            }
        }

        return $this;
    }

    protected function serializeToSnapshot($object)
    {
        $serialize = clone $object;

        $this->unburdenDependencies(
            $serialize,
            array_merge($this->serializeDependencies, ['changes' => ArrayCollection::class])
        );

        return $serialize;
    }
}
