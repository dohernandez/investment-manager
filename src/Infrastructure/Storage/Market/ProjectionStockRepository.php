<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Domain\Market\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionStockRepository extends ServiceEntityRepository implements
    ProjectionStockRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * @inheritDoc
     */
    public function findAllMatching(string $query, int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.name LIKE :name OR s.symbol LIKE :symbol')
            ->setParameter('name', '%'.$query.'%')
            ->setParameter('symbol', '%'.$query.'%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @inheritDoc
     */
    public function findBySymbol(string $symbol): ?Stock
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.symbol LIKE :symbol')
            ->setParameter('symbol', $symbol)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @inheritDoc
     */
    public function findAllListed(): array
    {
        return $this->findBy(['delisted' => 0]);
    }

    public function findAllMoversDaily(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.price', 'p')
            ->orderBy('p.changePercentage', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllShakersDaily(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.price', 'p')
            ->orderBy('p.changePercentage', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
}
