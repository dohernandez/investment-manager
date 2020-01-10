<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Domain\Market\StockMarket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StockMarket|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockMarket|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockMarket[]    findAll()
 * @method StockMarket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionStockMarketRepository extends ServiceEntityRepository implements
    ProjectionStockMarketRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockMarket::class);
    }
}
