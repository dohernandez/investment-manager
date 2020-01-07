<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Money;
use DateTime;

final class StockDividendProvider
{
    public static function provide(
        Stock $stock,
        Money $value,
        DateTime $exDate,
        string $status = StockDividend::STATUS_PROJECTED,
        ?DateTime $paymentDate = null,
        ?DateTime $recordDate = null
    ): StockDividend {
        return StockDividend::add($stock, $value, $exDate, $status, $paymentDate, $recordDate);
    }
}
