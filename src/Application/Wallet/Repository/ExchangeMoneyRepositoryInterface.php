<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\ExchangeRate;
use App\Infrastructure\Money\Currency;

interface ExchangeMoneyRepositoryInterface
{
    public function find(Currency $fromCurrency, Currency $toCurrency): ExchangeRate;
}
