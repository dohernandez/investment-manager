<?php

namespace App\Application\Market\Parser;

use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Currency;

final class YahooDividendsParser implements DividendsParserInterface
{
    /**
     * @inheritDoc
     */
    public function parser(Currency $currency, array $dividends): array
    {
        $stockDividends = [];

        foreach ($dividends as $dividend) {
            $stockDividend = new StockDividend();

            $stockDividends[] = $stockDividend;
        }

        return $stockDividends;
    }
}
