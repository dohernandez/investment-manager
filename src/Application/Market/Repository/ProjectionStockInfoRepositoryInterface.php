<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\StockInfo;

interface ProjectionStockInfoRepositoryInterface
{
    public function findByName(string $name): ?StockInfo;
}
