<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\Stock;
use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Money;

final class StockProvider
{
    public static function provide(
        string $name,
        string $symbol,
        StockMarket $market,
        ?Money $value = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null
    ): Stock {
        return Stock::add($name, $symbol, $market, $value, $description, $type, $sector, $industry);
    }
}
