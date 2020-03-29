<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Currency;

final class StockMarketProvider
{
    public static function provide(
        string $name,
        Currency $currency,
        string $country,
        string $symbol,
        ?string $yahooSymbol = null
    ): StockMarket {
        return StockMarket::register($name, $currency, $country, $symbol, $yahooSymbol);
    }
}
