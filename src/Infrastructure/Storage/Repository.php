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
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

use function array_merge;
use function get_class;
use function method_exists;
use function sprintf;

abstract class Repository
{
    /**
     * AggregateRoot already loaded to avoid doctrine load from query cache the wrong version.
     * @var ArrayCollection|AggregateRoot[]
     */
    protected $loaded;

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
    protected $snapshot;

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
            $object = $this->getReference($type, $id, $this->deserializeFromSnapshot($snapshot->getData()));
        } else {
            $object = $this->getReference($type, $id, new $type($id));
        }

        $changes = $this->eventSource->findEvents($id, $type, $object->getVersion());
        $this->overloadChangesDependencies($changes);

        $object->replay($changes);
        $this->loaded[$id] = $object;

        return $object;
    }

    protected function deserializeFromSnapshot($object)
    {
        $serialize = clone $object;

        $this->overloadDependencies($serialize, $this->serializeDependencies);

        return $serialize;
    }

    protected function getReference(string $type, string $id, AggregateRoot $data): AggregateRoot
    {
        $object = $this->em->getReference($type, $id);
        $this->em->refresh($object);

        if (get_class($object) !== get_class($data) && (!$object instanceof Proxy || get_parent_class($object) !== get_class($data))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Reference type %s is different than data type %s',
                    get_class($object),
                    get_class($data)
                )
            );
        }

        $reflect = $this->getAggregateRootReflect($object);
        $dataReflect = $this->getAggregateRootReflect($data);

        /** @var  $property */
        foreach ($dataReflect->getProperties() as $property) {
            if ($reflect->hasProperty($property->getName())) {
                $reflectionProperty = $reflect->getProperty($property->getName());
                $property->setAccessible(true);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $property->getValue($data));
            }
        }

        foreach ($dataReflect->getParentClass()->getProperties() as $property) {
            if ($property->getName() !== 'id' && $reflect->getParentClass()->hasProperty($property->getName())) {
                $reflectionProperty = $reflect->getParentClass()->getProperty($property->getName());
                $property->setAccessible(true);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $property->getValue($data));
            }
        }

        return $object;
    }

    private function getAggregateRootReflect(AggregateRoot $object): ReflectionClass
    {
        $reflect = new ReflectionClass(get_class($object));
        // This gets the real object, the one that the Doctrine\Common\Persistence\Proxy extends
        if ($object instanceof Proxy) {
            $reflect = $reflect->getParentClass();
        }

        return $reflect;
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
            if ($class === ArrayCollection::class) {
                continue;
            }

            $reflectionProperty = null;
            if ($reflect->hasProperty($property)) {
                $reflectionProperty = $reflect->getProperty($property);
            } elseif ($reflect->getParentClass() && $reflect->getParentClass()->hasProperty($property)) {
                $reflectionProperty = $reflect->getParentClass()->getProperty($property);
            }

            if($reflectionProperty) {
                $reflectionProperty->setAccessible(true);

                $value = $reflectionProperty->getValue($object);
                if ($value) {
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
            $this->eventSource->saveEvents($changes, true);

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

//    protected function persistProjection(AggregateRoot $object)
//    {
//        $projection = $this->em->find($object->getAggregateType(), $object->getId());
//        if (!$projection) {
//            $projection = clone $object;
//        } else {
//
//        }
//
//        $this->em->persist($projection);
//        $this->em->flush();
//    }

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

                if ($class === ArrayCollection::class) {
                    $value = new $class();
                    $reflectionProperty->setValue($object, $value);

                    continue;
                }

                $value = $reflectionProperty->getValue($object);
                if ($value && method_exists($value, 'getId')) {
                    $value = new $class($value->getId());
                    $reflectionProperty->setValue($object, $value);
                }
            }
        }

        return $this;
    }

    protected function serializeToSnapshot($object)
    {
        $serialize = clone $object;

        if ($serialize instanceof Proxy) {
            $serialize = $this->extractObjectFromProxy($serialize);
        }

        $this->unburdenDependencies(
            $serialize,
            array_merge($this->serializeDependencies, ['changes' => ArrayCollection::class])
        );

        return $serialize;
    }

    private function extractObjectFromProxy(AggregateRoot $proxyObject): AggregateRoot
    {
        $reflect = new ReflectionClass(get_class($proxyObject));
        $reflect = $reflect->getParentClass();

        $class = $reflect->getName();
        $object = new $class($proxyObject->getId());

        foreach ($reflect->getProperties() as $property) {
            if ($property->getName() === 'id') {
                continue;
            }

            $reflectionProperty = $reflect->getProperty($property->getName());
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, $reflectionProperty->getValue($proxyObject));
        }

        return $object;
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
}
