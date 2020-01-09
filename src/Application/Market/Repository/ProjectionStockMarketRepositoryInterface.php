<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\StockMarket;

interface ProjectionStockMarketRepositoryInterface
{
    public function find(string $id): StockMarket;
}
