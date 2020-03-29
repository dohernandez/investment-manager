<?php

namespace App\Application\ExchangeMoney\Repository;

use App\Domain\ExchangeMoney\Rate;

interface ExchangeMoneyRepositoryInterface
{
    public function saveRate(Rate $rate);

    public function findRateByPaarCurrency(string $paarCurrency): ?Rate;

    public function findAllByToCurrency(string $toCurrency): array;
}
