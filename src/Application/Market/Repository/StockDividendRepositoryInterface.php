<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use DateTime;

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

    public function findLastBeforeExDateByStock(Stock $stock, DateTime $date): ?StockDividend;

    /**
     * @param Stock $stock
     * @param DateTime $dateFrom
     * @param DateTime|null $dateTo
     *
     * @return StockDividend[]
     */
    public function findAllExDateTimeWindow(Stock $stock, DateTime $dateFrom, ?DateTime $dateTo = null): array;
}
