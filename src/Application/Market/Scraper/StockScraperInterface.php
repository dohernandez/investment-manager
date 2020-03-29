<?php

namespace App\Application\Market\Scraper;

interface StockScraperInterface
{
    public function scrap(string $symbol, ?string $yahooSymbol = null): StockCrawled;
}
