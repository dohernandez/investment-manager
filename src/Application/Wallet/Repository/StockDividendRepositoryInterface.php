<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\StockDividend;
use DateTime;

interface StockDividendRepositoryInterface
{
    public function findLastBeforeExDateByStock(string $id, DateTime $date): ?StockDividend;

    /**
     * @param string $id
     * @param DateTime $dateFrom
     * @param DateTime|null $dateTo
     *
     * @return StockDividend[]
     */
    public function findAllExDateTimeWindow(string $id, DateTime $dateFrom, ?DateTime $dateTo = null): array;
}
