<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockHistoricalPriceRepositoryInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\MarketData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MarketData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarketData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarketData[]    findAll()
 * @method MarketData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockHistoricalPriceRepository extends ServiceEntityRepository  implements StockHistoricalPriceRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MarketData::class);
    }

    /**
     * @inheritDoc
     */
    public function findAllByStock(Stock $stock): ?array
    {
        return $this->createQueryBuilder('md')
            ->andWhere('md.stock = :stock')
            ->setParameter('stock', $stock)
            ->getQuery()
            ->getResult();
    }
}
