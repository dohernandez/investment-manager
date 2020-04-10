<?php

namespace App\Application\Market\Parser;

use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Currency;

interface DividendsParserInterface
{
    /**
     * @param Currency $currency
     * @param array $dividends
     *
     * @return StockDividend[]
     */
    public function parser(Currency $currency, array $dividends): array;
}
