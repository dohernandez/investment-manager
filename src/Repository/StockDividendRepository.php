<?php

namespace App\Repository;

use App\Entity\Stock;
use App\Entity\StockDividend;
use App\Repository\Criteria\StockDividendByCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StockDividend|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockDividend|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockDividend[]    findAll()
 * @method StockDividend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockDividendRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockDividend::class);
    }

    /**
     * Find the next dividend announce or projected for the given stock
     *
     * @param Stock $stock
     * @param \DateTimeInterface $exDate
     *
     * @return StockDividend|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findNextByStock(Stock $stock, \DateTimeInterface $exDate = null): ?StockDividend
    {
        if ($exDate === null) {
            $exDate = new \DateTime();
        }

        return $this->createQueryBuilder('sd')
            ->andWhere('sd.stock = :stock')
            ->setParameter('stock', $stock)
            ->addCriteria(StockDividendByCriteria::nextExDate($exDate))
            ->orderBy('sd.exDate', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    // /**
    //  * @return StockDividend[] Returns an array of StockDividend objects
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
    public function findOneBySomeField($value): ?StockDividend
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
