<?php

namespace App\Infrastructure\Storage;

use App\Domain\Wallet\Event\WalletDividendProjectedUpdated;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Doctrine\DBAL\DataReferenceInterface;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\Snapshot;
use App\Infrastructure\EventSource\SnapshotRepositoryInterface;
use ArrayAccess;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
            $data = $snapshot->getData();
            $this->overloadReferences($data);

            $object = $this->getAggregateReference($type, $id, $data, $snapshot->getVersion());
        } else {
            $object = $this->getAggregateReference($type, $id);
        }

        $changes = $this->eventSource->findEvents($id, $type, $object->getVersion());
        $this->overloadChangesDependencies($changes);

        $object->replay($changes);
        $this->loaded[$id] = $object;

        return $object;
    }

    protected function getAggregateReference(string $type, string $id, ?AggregateRoot $data = null, int $version = 0): AggregateRoot
    {
        $object = $this->em->getReference($type, $id);
        $this->em->refresh($object);

        $reflect = $this->getAggregateRootReflect($object);

        $versionProperty = $reflect->getParentClass()->getProperty('version');
        $versionProperty->setAccessible(true);
        $versionProperty->setValue($object, $version);

        if (!$data) {
            return $object;
        }

        if (get_class($object) !== get_class($data) && (!$object instanceof Proxy || get_parent_class($object) !== get_class($data))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Reference type %s is different than data type %s',
                    get_class($object),
                    get_class($data)
                )
            );
        }

        // Updating aggregate root properties
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

            $this->overloadReferences($payload);
        }

        return $this;
    }

    private function overloadReferences($object): self
    {
        $reflect = new ReflectionClass(get_class($object));
        // This gets the real object, the one that the Doctrine\Common\Persistence\Proxy extends
        if ($object instanceof Proxy) {
            $reflect = $reflect->getParentClass();
        }

        foreach ($reflect->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            $this->bindReferences($value);
            $property->setValue($object, $value);
        }

        return $this;
    }

    private function bindReferences(&$value): self
    {
        if (!$value) {
            return $this;
        }

        if ($value instanceof DataReferenceInterface) {
            $class = get_class($value);
            if ($value instanceof Proxy) {
                $class = get_parent_class($value);
            }

            $value = $this->em->find($class, $value->getId());

            return $this;
        }

        if ($value instanceof Collection) {
            foreach ($value as $k => $item) {
                static::bindReferences($item);

                $value->set($k, $item);
            }

            return $this;
        }

        if (is_array($value)) {
            foreach ($value as $k => $item) {
                static::bindReferences($item);

                $value[$k] = $item;
            }

            return $this;
        }

        return $this;
    }

    protected function store(AggregateRoot $object)
    {
        $changes = $object->getChanges();
        if ($changes && !$changes->isEmpty()) {
            $this->em->persist($object);
            $this->em->flush();

            $this->eventSource->saveEvents($changes, true);

            if (!($object->getVersion() % 5)) {
                $this->snapshot->save(
                    (new Snapshot($object->getId()))
                        ->setType($object->getAggregateType())
                        ->setVersion($object->getVersion())
                        ->setData($object)
                );
            }
        }

        $this->loaded[$object->getId()] = $object;
    }
}
