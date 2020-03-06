<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Currency;

final class Rate
{
    /**
     * @var Currency
     */
    private $fromCurrency;

    /**
     * @var Currency
     */
    private $toCurrency;

    /**
     * @var float
     */
    private $rate;

    public function __construct(Currency $fromCurrency, Currency $toCurrency, float $rate)
    {
        $this->fromCurrency = $fromCurrency;
        $this->toCurrency = $toCurrency;
        $this->rate = $rate;
    }

    public function getFromCurrency(): ?Currency
    {
        return $this->fromCurrency;
    }

    public function getToCurrency(): ?Currency
    {
        return $this->toCurrency;
    }

    public function getRate()
    {
        return $this->rate;
    }
}
