<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use DateTime;

interface StockHistoricalPriceRepositoryInterface
{
    /**
     * @param Stock $stock
     *
     * @return StockDividend[]|null
     */
    public function findAllByStock(Stock $stock): ?array;
}
