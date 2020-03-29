<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\StockInfo;

interface StockInfoRepositoryInterface
{
    public function find(string $id): StockInfo;

    public function save(StockInfo $stockInfo);
}
