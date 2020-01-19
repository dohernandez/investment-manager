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
        return (new StockDividend())
            ->setStock($stock)
            ->setValue($value)
            ->setExDate($exDate)
            ->setStatus($status)
            ->setPaymentDate($paymentDate)
            ->setRecordDate($recordDate);
    }
}
