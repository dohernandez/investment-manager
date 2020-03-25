<?php

namespace App\Application\Market\Command;

use App\Infrastructure\Money\Currency;

final class LoadYahooStockMarketQuote
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var string
     */
    private $symbol;

    public function __construct(Currency $currency, string $symbol)
    {
        $this->currency = $currency;
        $this->symbol = $symbol;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
