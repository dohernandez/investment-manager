<?php

namespace App\Infrastructure\Storage;

use App\Domain\Market\Stock;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\Storage\Market\StockRepository;
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

    public function __construct(EntityManagerInterface $em, EventSourceRepositoryInterface $eventSource)
    {
        $this->em = $em;
        $this->eventSource = $eventSource;
    }

    /**
     * @param string $class
     * @param string $id
     *
     * @return mixed object
     * @throws ReflectionException
     */
    protected function load(string $class, string $id)
    {
        if (isset($this->loaded[$id])) {
            return $this->loaded[$id];
        }

        $changes = $this->eventSource->findEvents($id, $class);
        $this->overloadDependencies($changes);

        $object = (new $class($id))->replay($changes);

        $object = $this->em->merge($object);
        $this->loaded[$id] = $object;

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
        if (!$changes->isEmpty()) {

            $this->em->persist($object);
            $this->em->flush();

            $this->unburdenDependencies($changes);
            $this->eventSource->saveEvents($changes);
            $this->em->flush();
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
}
