<?php

namespace App\Domain\Market;

final class StockMetadata
{
    /**
     * @var string|null
     */
    private $dividendFrequency;

    /**
     * @var float|null
     */
    private $dividendYield;

    /**
     * @var string|null
     */
    private $yahooSymbol;

    public function getDividendFrequency(): ?string
    {
        return $this->dividendFrequency;
    }

    public function getDividendYield(): ?float
    {
        return $this->dividendYield;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }

    public function updateDividendYield(?float $dividendYield): self
    {
        $self = clone $this;
        $self->dividendYield = $dividendYield;

        return $self;
    }

    public function updateYahooSymbol(?string $yahooSymbol): self
    {
        $self = clone $this;
        $self->yahooSymbol = $yahooSymbol;

        return $self;
    }

    public function updateDividendFrequency(?string $frequency): self
    {
        $self = clone $this;
        $self->dividendFrequency = $frequency;

        return $self;
    }
}
