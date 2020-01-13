<?php

namespace App\Domain\Market;

use App\Infrastructure\Money\Money;

final class StockMetadata
{
    /**
     * @var StockPrice
     */
    private $price;

    /**
     * @var float
     */
    private $dividendYield;

    /**
     * @var string
     */
    private $yahooSymbol;

    public function __construct(
        StockPrice $price,
        float $dividendYield,
        string $yahooSymbol
    ) {
        $this->price = $price;
        $this->dividendYield = $dividendYield;
        $this->yahooSymbol = $yahooSymbol;
    }

    public function getPrice(): StockPrice
    {
        return $this->price;
    }

    public function getDividendYield(): float
    {
        return $this->dividendYield;
    }

    public function getYahooSymbol(): string
    {
        return $this->yahooSymbol;
    }

    public function updateDividendYield(float $dividendYield): self
    {
        return new static($this->price, $dividendYield, $this->yahooSymbol);
    }

    public function updatePrice(StockPrice $price): self
    {
        return new static($price, $this->dividendYield, $this->yahooSymbol);
    }

    public function updateYahooSymbol(string $yahooSymbol): self
    {
        return new static($this->price, $this->dividendYield, $yahooSymbol);
    }
}
