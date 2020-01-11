<?php

namespace App\Application\Market\Command;

use App\Infrastructure\Money\Currency;

final class RegisterStockMarket
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string
     */
    private $yahooSymbol;

    public function __construct(string $name, Currency $currency, string $country, string $symbol, ?string $yahooSymbol)
    {
        $this->name = $name;
        $this->currency = $currency;
        $this->country = $country;
        $this->symbol = $symbol;
        $this->yahooSymbol = $yahooSymbol;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }
}
