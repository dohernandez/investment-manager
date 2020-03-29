<?php

namespace App\Application\Market\Parser;

use App\Domain\Market\StockDividend;

interface DividendsParserInterface
{
    /**
     * @param array $dividends
     *
     * @return StockDividend[]
     */
    public function parser(array $dividends): array;
}
