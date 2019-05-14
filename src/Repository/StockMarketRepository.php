<?php

namespace App\Repository;

use App\Entity\StockMarket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StockMarket|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockMarket|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockMarket[]    findAll()
 * @method StockMarket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockMarketRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockMarket::class);
    }

    public function findAllMatching($query, int $limit = 5)
    {
        return $this->createQueryBuilder('sm')
            ->andWhere('sm.symbol LIKE :symbol OR sm.name LIKE :name')
            ->setParameter('symbol', '%'.$query.'%')
            ->setParameter('name', '%'.$query.'%')
            ->orderBy('sm.symbol', 'ASC ')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return StockMarket[] Returns an array of StockMarket objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StockMarket
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
