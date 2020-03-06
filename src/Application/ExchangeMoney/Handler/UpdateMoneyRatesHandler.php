<?php

namespace App\Application\ExchangeMoney\Handler;

use App\Application\ExchangeMoney\Command\UpdateMoneyRates;
use App\Application\ExchangeMoney\Exchange\ExchangeMoneyInterface;
use App\Application\ExchangeMoney\Repository\ExchangeMoneyRepositoryInterface;
use App\Domain\ExchangeMoney\Rate;
use App\Infrastructure\Money\Currency;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use function explode;

final class UpdateMoneyRatesHandler implements MessageHandlerInterface
{
    /**
     * @var ExchangeMoneyRepositoryInterface
     */
    private $exchangeMoneyRepository;

    /**
     * @var ExchangeMoneyInterface
     */
    private $exchangeMoney;

    public function __construct(
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository,
        ExchangeMoneyInterface $exchangeMoney
    )
    {
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
        $this->exchangeMoney = $exchangeMoney;
    }

    public function __invoke(UpdateMoneyRates $message)
    {
        $rates = $this->exchangeMoney->getCurrencyRate($message->getPaarCurrencies());

        foreach ($message->getPaarCurrencies() as $paarCurrency) {
            $rate = $this->exchangeMoneyRepository->findRateByPaarCurrency($paarCurrency);

            if ($rate === null) {
                list($fromCurrency, $toCurrency) = explode('_', $paarCurrency);
                $rate = (new Rate())
                    ->setFromCurrency(Currency::fromCode($fromCurrency))
                    ->setToCurrency(Currency::fromCode($toCurrency))
                    ->setPaarCurrency($paarCurrency)
                ;
            }

            $rate->setRate($rates[$paarCurrency]);

            $this->exchangeMoneyRepository->saveRate($rate);
        }
    }
}
