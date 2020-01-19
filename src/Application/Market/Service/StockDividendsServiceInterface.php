<?php

namespace App\Application\Market\Service;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;

interface StockDividendsServiceInterface
{
    /**
     * @param Stock $stock
     *
     * @return StockDividend[]
     */
    public function getStockDividends(Stock $stock): array;
}
