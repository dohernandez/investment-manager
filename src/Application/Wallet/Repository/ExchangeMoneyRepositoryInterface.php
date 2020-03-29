<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Rate;
use App\Infrastructure\Money\Currency;

interface ExchangeMoneyRepositoryInterface
{
    public function findRate(Currency $fromCurrency, Currency $toCurrency): ?Rate;

    public function findAllByToCurrency(Currency $toCurrency): array;
}
