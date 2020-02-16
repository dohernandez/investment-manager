<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Domain\Wallet\ExchangeRate;
use App\Infrastructure\Money\Currency;

final class ExchangeMoneyRepository implements ExchangeMoneyRepositoryInterface
{
    public function find(Currency $fromCurrency, Currency $toCurrency): ExchangeRate
    {
        return new ExchangeRate($fromCurrency, $toCurrency, 1);
    }
}
