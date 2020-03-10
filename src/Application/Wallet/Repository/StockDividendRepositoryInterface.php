<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\StockDividend;
use DateTime;

interface StockDividendRepositoryInterface
{
    public function findLastBeforeDateByStock(string $id, DateTime $date): ?StockDividend;
}
