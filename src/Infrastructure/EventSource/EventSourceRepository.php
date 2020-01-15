<?php

namespace App\Infrastructure\EventSource;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

final class EventSourceRepository extends ServiceEntityRepository implements EventSourceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Changed::class);
    }

    /**
     * @inheritDoc
     */
    public function findEvents(string $id, string $typeName, int $fromNumber = 1, int $count = null): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.aggregateId = :id')
            ->setParameter('id', $id)
            ->andWhere('c.aggregateType = :typeName')
            ->setParameter('typeName', $typeName)
            ->andWhere('c.aggregateVersion >= :fromNumber')
            ->setParameter('fromNumber', $fromNumber)
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @inheritDoc
     */
    public function saveEvents(ArrayCollection $changes)
    {
        foreach ($changes as $change) {
            $this->_em->persist($change);
        }
    }
}
