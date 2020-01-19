<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockDividendRepositoryInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StockDividend|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockDividend|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockDividend[]    findAll()
 * @method StockDividend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockDividendRepository extends ServiceEntityRepository  implements StockDividendRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockDividend::class);
    }

    /**
     * @inheritDoc
     */
    public function findAllByStock(Stock $stock): ?array
    {
        return $this->createQueryBuilder('sd')
            ->andWhere('sd.stock = :stock')
            ->setParameter('stock', $stock)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findAllProjectedAndAnnouncedByStock(Stock $stock): ?array
    {
        return $this->createQueryBuilder('sd')
            ->andWhere('sd.stock = :stock')
            ->setParameter('stock', $stock)
            ->andWhere('sd.status LIKE :status_projected or sd.status LIKE :status_announced')
            ->setParameter('status_projected', StockDividend::STATUS_PROJECTED)
            ->setParameter('status_announced', StockDividend::STATUS_ANNOUNCED)
            ->getQuery()
            ->getResult();
    }
}
