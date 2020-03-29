<?php

namespace App\Application\ExchangeMoney\Command;

final class UpdateMoneyRates
{
    /**
     * @var array
     */
    private $paarCurrencies;

    public function __construct(array $paarCurrencies)
    {
        $this->paarCurrencies = $paarCurrencies;
    }

    public function getPaarCurrencies(): array
    {
        return $this->paarCurrencies;
    }
}
