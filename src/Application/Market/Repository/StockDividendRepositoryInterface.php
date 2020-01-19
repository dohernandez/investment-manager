<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;

interface StockDividendRepositoryInterface
{
    /**
     * @param Stock $stock
     *
     * @return StockDividend[]|null
     */
    public function findAllByStock(Stock $stock): ?array;

    /**
     * @param Stock $stock
     *
     * @return StockDividend[]|null
     */
    public function findAllProjectedAndAnnouncedByStock(Stock $stock): ?array;
}
