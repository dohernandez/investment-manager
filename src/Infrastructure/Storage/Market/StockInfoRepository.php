<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Domain\Market\StockInfo;
use App\Infrastructure\EventSource\EventSourceRepository;
use App\Infrastructure\Storage\Repository;
use Doctrine\ORM\EntityManagerInterface;

final class StockInfoRepository extends Repository implements StockInfoRepositoryInterface
{
    public function find(string $id): StockInfo
    {
        return $this->load(StockInfo::class, $id);
    }

    public function save(StockInfo $stockInfo)
    {
        $this->store($stockInfo);
    }
}
