<?php

namespace App\Application\ExchangeMoney\Repository;

use App\Domain\ExchangeMoney\Rate;
use DateTime;

interface ExchangeMoneyRepositoryInterface
{
    public function saveRate(Rate $rate);

    public function findRateByPaarCurrency(string $paarCurrency): ?Rate;

    /**
     * @param string $toCurrency
     *
     * @return Rate[]
     */
    public function findAllByToCurrency(string $toCurrency): array;

    /**
     * @return Rate[]
     */
    public function findAllLatest();

    public function findRateByPaarCurrencyDateAt(string $paarCurrency, DateTime $date): ?Rate;
}
