<?php

namespace App\Domain\ExchangeMoney;

use App\Infrastructure\Money\Currency;

final class Market
{
    /**
     * @var Currency
     */
    private $currency;

    public function __construct(Currency $currency)
    {
        $this->currency = $currency;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
