<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Application\ExchangeMoney\Repository\ExchangeMoneyRepositoryInterface as ProjectionExchangeMoneyRepositoryInterface;
use App\Domain\Wallet\Rate;
use App\Infrastructure\Exception\NotFoundException;
use App\Infrastructure\Money\Currency;

final class ExchangeMoneyRepository implements ExchangeMoneyRepositoryInterface
{
    /**
     * @var ProjectionExchangeMoneyRepositoryInterface
     */
    private $projectionExchangeMoneyRepository;

    public function __construct(ProjectionExchangeMoneyRepositoryInterface $projectionExchangeMoneyRepository)
    {
        $this->projectionExchangeMoneyRepository = $projectionExchangeMoneyRepository;
    }

    public function findRate(Currency $fromCurrency, Currency $toCurrency): Rate
    {
        $paar = $toCurrency->getCurrencyCode() . '_' . $fromCurrency->getCurrencyCode();
        $rate = $this->projectionExchangeMoneyRepository->findRateByPaarCurrency($paar);

        if (!$rate) {
            throw new NotFoundException('Not found exchange money rate', [
                'paar' => $paar
            ]);
        }

        return new Rate($fromCurrency, $toCurrency, $rate->getRate());
    }
}
