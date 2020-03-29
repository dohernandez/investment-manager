<?php

namespace App\Application\ExchangeMoney\Exchange;

interface ExchangeMoneyInterface
{
    public function getCurrencyRate(array $paarCurrencies): array;
}
