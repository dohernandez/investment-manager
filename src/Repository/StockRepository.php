<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * @param int|null $limit
     *
     * @return Stock[]
     */
    public function findAll(?int $limit=null)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.market', 'm')
            ->addSelect('m')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param string $query
     * @param int $limit
     *
     * @return Stock[]
     */
    public function findAllMatchingOrAll(string $query = null, int $limit = 5): array
    {
        if ($query !== null ) {
                return $this->findAllMatching($query, $limit);
        }

        return $this->findAll($limit);
    }

    /**
     * @param string $query
     * @param int $limit
     *
     * @return Stock[]
     */
    public function findAllMatching(string $query, int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.name LIKE :name OR s.symbol LIKE :symbol')
            ->setParameter('name', '%'.$query.'%')
            ->setParameter('symbol', '%'.$query.'%')
            ->innerJoin('s.market', 'm')
            ->addSelect('m')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Stock[] Returns an array of Stock objects
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
    public function findOneBySomeField($value): ?Stock
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
