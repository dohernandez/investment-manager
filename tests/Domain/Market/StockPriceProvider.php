<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\StockPrice;
use App\Infrastructure\Money\Money;

final class StockPriceProvider
{
    public static function provide(
        Money $value,
        ?Money $changePrice = null,
        ?Money $preClose = null,
        ?Money $open = null,
        ?float $peRatio = null,
        ?Money $dayLow = null,
        ?Money $dayHigh = null,
        ?Money $week52Low = null,
        ?Money $week52High = null,
        ?int $id = null
    ): StockPrice {
        $price = (new StockPrice())
            ->setPrice($value)
            ->setChangePrice($changePrice)
            ->setPeRatio($peRatio)
            ->setPreClose($preClose)
            ->setOpen($open)
            ->setDayLow($dayLow)
            ->setDayHigh($dayHigh)
            ->setWeek52Low($week52Low)
            ->setWeek52High($week52High);

        if (!$id) {
            $reflectionClass = new \ReflectionClass(StockPrice::class);
            $reflectionProperty = $reflectionClass->getProperty('id');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($price, $id);
        }

        return $price;
    }
}
