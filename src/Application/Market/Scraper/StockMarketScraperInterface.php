<?php

namespace App\Application\Market\Scraper;

use App\Infrastructure\Money\Currency;

interface StockMarketScraperInterface
{
    public function scrap(Currency $currency, string $symbol): StockCrawled;
}
