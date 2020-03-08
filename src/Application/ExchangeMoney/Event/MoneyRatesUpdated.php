<?php

namespace App\Application\ExchangeMoney\Event;

use App\Domain\Wallet\Rate;
use App\Infrastructure\Money\Currency;

final class MoneyRatesUpdated
{
    /**
     * @var Rate[]
     */
    private $moneyExchangeRates;

    public function __construct(array $moneyExchangeRates)
    {
        $this->moneyExchangeRates = $moneyExchangeRates;
    }

    /**
     * @return Rate[]
     */
    public function getMoneyExchangeRates(): array
    {
        return $this->moneyExchangeRates;
    }
}
