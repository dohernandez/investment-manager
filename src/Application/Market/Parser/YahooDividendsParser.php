<?php

namespace App\Application\Market\Parser;

use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Money;
use DateTime;

final class YahooDividendsParser implements DividendsParserInterface
{
    /**
     * @inheritDoc
     */
    public function parser(array $dividends): array
    {
        $stockDividends = [];

        foreach ($dividends as $dividend) {
            $stockDividend = new StockDividend();

            $stockDividends[] = $stockDividend;
        }

        return $stockDividends;
    }
}
