<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use App\Domain\Market\StockInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StockInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockInfo[]    findAll()
 * @method StockInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionStockInfoRepository extends ServiceEntityRepository implements
    ProjectionStockInfoRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockInfo::class);
    }

    public function findByName(string $name): ?StockInfo
    {
        return $this->findOneBy(['name' => $name]);
    }
}
