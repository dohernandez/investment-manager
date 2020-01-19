<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\HouseKeeperRepositoryInterface;
use App\Domain\Market\StockDividend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class HouseKeeperStockDividendRepository extends ServiceEntityRepository implements
    HouseKeeperRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockDividend::class);
    }

    public function clean()
    {
        $this->_em->createQueryBuilder()
            ->delete($this->_entityName, 'sd')
            ->andWhere('sd.stock is null')
            ->getQuery()
            ->execute();
    }
}
