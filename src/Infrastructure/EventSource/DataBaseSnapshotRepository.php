<?php

namespace App\Infrastructure\EventSource;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class DataBaseSnapshotRepository extends ServiceEntityRepository implements SnapshotRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Snapshot::class);
    }

    public function save(Snapshot $snapshot)
    {
        $snapshot = $this->_em->merge($snapshot);
        $this->_em->persist($snapshot);
        $this->_em->flush();
    }

    /**
     * @inheritDoc
     */
    public function load(string $id, string $type): ?Snapshot
    {
        return $this->_em->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->where('type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
