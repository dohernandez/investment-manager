<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Domain\Wallet\Rate;
use App\Infrastructure\Money\Currency;

final class ExchangeMoneyRepository implements ExchangeMoneyRepositoryInterface
{
    /**
     * @var ExchangeMoneyRepositoryInterface
     */
    private $exchangeMoneyRepository;

    public function __construct(ExchangeMoneyRepositoryInterface $exchangeMoneyRepository)
    {
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
    }

    public function findRate(Currency $fromCurrency, Currency $toCurrency): Rate
    {
        return new Rate($fromCurrency, $toCurrency, 1);
    }
}
