<?php

namespace App\Application\ExchangeMoney\Exchange;

use DateTimeInterface;

interface ExchangeMoneyInterface
{
    public function getCurrencyRate(array $paarCurrencies): array;

    public function getCurrencyRateHistorical(
        array $paarCurrencies,
        DateTimeInterface $startDate = null,
        DateTimeInterface $endDate = null
    ): array;
}
