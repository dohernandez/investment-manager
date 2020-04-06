<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Money;

final class ExchangeTicket
{
    /**
     * @var Money
     */
    private $moneyOriginalCurrency;

    /**
     * @var Rate|null
     */
    private $rate;

    /**
     * @var Money
     */
    private $money;

    public function __construct(Rate $rate, Money $moneyOriginalCurrency, Money $money)
    {
        $this->moneyOriginalCurrency = $moneyOriginalCurrency;
        $this->rate = $rate;
        $this->money = $money;
    }

    public function getMoneyOriginalCurrency(): Money
    {
        return $this->moneyOriginalCurrency;
    }

    public function getRate(): ?Rate
    {
        return $this->rate;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }
}
