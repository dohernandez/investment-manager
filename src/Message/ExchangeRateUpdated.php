<?php

namespace App\Message;

final class ExchangeRateUpdated
{
    /**
     * @var array
     */
    private $exchangeRates;

    public function __construct(array $exchangeRates)
    {
        $this->exchangeRates = $exchangeRates;
    }

    public function getExchangeRates(): array
    {
        return $this->exchangeRates;
    }
}
