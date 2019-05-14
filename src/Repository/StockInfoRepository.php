<?php

namespace App\Repository;

use App\Entity\StockInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StockInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockInfo[]    findAll()
 * @method StockInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockInfoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockInfo::class);
    }

    // /**
    //  * @return StockInfo[] Returns an array of StockInfo objects
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
    public function findOneBySomeField($value): ?StockInfo
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
