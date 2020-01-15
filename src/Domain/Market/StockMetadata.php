<?php

namespace App\Domain\Market;

final class StockMetadata
{
    /**
     * @var StockPrice|null
     */
    private $price;

    /**
     * @var float|null
     */
    private $dividendYield;

    /**
     * @var string|null
     */
    private $yahooSymbol;

    public function getPrice(): ?StockPrice
    {
        return $this->price;
    }

    public function getDividendYield(): ?float
    {
        return $this->dividendYield;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }

    public function updateDividendYield(float $dividendYield): self
    {
        $self = clone $this;
        $self->dividendYield = $dividendYield;

        return $self;
    }

    public function updatePrice(StockPrice $price): self
    {
        $self = clone $this;
        $self->price = $price;

        return $self;
    }

    public function updateYahooSymbol(string $yahooSymbol): self
    {
        $self = clone $this;
        $self->yahooSymbol = $yahooSymbol;

        return $self;
    }
}
