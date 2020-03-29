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

    public function __construct(
      ?string $yahooSymbol = null,
      ?float $dividendYield = null,
      ?string $dividendFrequency = null
    ) {
        $this->yahooSymbol = $yahooSymbol;
        $this->dividendYield = $dividendYield;
        $this->dividendFrequency = $dividendFrequency;
    }

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

    public function changeDividendYield(?float $dividendYield): self
    {
        return new static(
            $this->getYahooSymbol(),
            $dividendYield,
            $this->getDividendFrequency()
        );
    }

    public function changeYahooSymbol(?string $yahooSymbol): self
    {
        return new static(
            $yahooSymbol,
            $this->getDividendYield(),
            $this->getDividendFrequency()
        );
    }

    public function changeDividendFrequency(?string $frequency): self
    {
        return new static(
            $this->getYahooSymbol(),
            $this->getDividendYield(),
            $frequency
        );
    }
}
