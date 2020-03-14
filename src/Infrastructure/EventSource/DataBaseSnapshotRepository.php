<?php

namespace App\Infrastructure\EventSource;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

final class DataBaseSnapshotRepository extends ServiceEntityRepository implements SnapshotRepositoryInterface
{
    /**
     * Snapshot already loaded to avoid doctrine load from query cache the wrong version.
     * @var ArrayCollection|Snapshot[]
     */
    private $loaded;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Snapshot::class);
    }

    public function save(Snapshot $snapshot)
    {
        if ($object = $this->load($snapshot->getId(), $snapshot->getType())) {
            $object->setData($snapshot->getData());
            $snapshot = $object;
        }

        $this->_em->persist($snapshot);
        $this->_em->flush();

        $this->loaded[$snapshot->getId()] = $snapshot;
    }

    /**
     * @inheritDoc
     */
    public function load(string $id, string $type): ?Snapshot
    {
        if (isset($this->loaded[$id])) {
            return $this->loaded[$id];
        }

        $snapshot = $this->createQueryBuilder('s')
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
            ->andWhere('s.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();

        $this->loaded[$id] = $snapshot;

        return $snapshot;
    }
}
