<?php

namespace App\Infrastructure\EventSource;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class EventSourceRepository extends ServiceEntityRepository implements EventSourceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Changed::class);
    }

    public function load(string $id, string $typeName, int $fromNumber = 1, int $count = null)
    {
        /** @var Changed[] $changes */
        $changes = $this->createQueryBuilder('c')
            ->andWhere('c.aggregateId = :id')
            ->setParameter('id', $id)
            ->andWhere('c.aggregateType = :typeName')
            ->setParameter('typeName', $typeName)
            ->andWhere('c.aggregateVersion >= :fromNumber')
            ->setParameter('fromNumber', $fromNumber)
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();

        if (empty($changes)) {
            return null;
        }

        /** @var AggregateRoot $aggregate */
        $aggregate = new $typeName($id);
        $aggregate->replay($changes);

        return $aggregate;
    }

    public function store(AggregateRoot $aggregateRoot)
    {
        $em = $this->getEntityManager();

        foreach ($aggregateRoot->getChanges() as $change) {
            $em->persist($change);
        }

        $em->flush();
    }
}
