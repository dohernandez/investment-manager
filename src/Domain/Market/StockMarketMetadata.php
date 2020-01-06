<?php

namespace App\Domain\Market;

final class StockMarketMetadata
{
    /**
     * @var string
     */
    private $yahooSymbol;

    public function __construct(?string $yahooSymbol)
    {
        $this->yahooSymbol = $yahooSymbol;
    }

    /**
     * @return string
     */
    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }

    public function changeYahooSymbol(?string $yahooSymbol): self
    {
        return new static($yahooSymbol);
    }
}
