<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\StockMarket;

interface StockMarketRepositoryInterface
{
    public function find(string $id): StockMarket;

    public function save(StockMarket $market);
}
