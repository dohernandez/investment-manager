<?php

namespace App\Application\Market\Command;

final class LoadYahooQuote
{
    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string|null
     */
    private $yahooSymbol;

    public function __construct(string $symbol, ?string $yahooSymbol = null)
    {
        $this->symbol = $symbol;
        $this->yahooSymbol = $yahooSymbol;
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
