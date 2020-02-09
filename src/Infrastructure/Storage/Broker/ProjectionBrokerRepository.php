<?php

namespace App\Infrastructure\Storage\Broker;

use App\Application\Broker\Repository\ProjectionBrokerRepositoryInterface;
use App\Domain\Broker\Broker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Broker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Broker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Broker[]    findAll()
 * @method Broker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionBrokerRepository extends ServiceEntityRepository implements ProjectionBrokerRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Broker::class);
    }

    /**
     * @inheritDoc
     */
    public function findAllMatching(string $query, int $limit = 5)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.name LIKE :name')
            ->setParameter('name', '%'.$query.'%')
            ->orderBy('b.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
}
