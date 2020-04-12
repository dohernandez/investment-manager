<?php

namespace App\Application\ExchangeMoney\Handler;

use App\Application\ExchangeMoney\Command\UpdateMoneyRates;
use App\Application\ExchangeMoney\Event\MoneyRatesUpdated;
use App\Application\ExchangeMoney\Exchange\ExchangeMoneyInterface;
use App\Application\ExchangeMoney\Repository\ExchangeMoneyRepositoryInterface;
use App\Domain\ExchangeMoney\Rate;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Money\Currency;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository,
        ExchangeMoneyInterface $exchangeMoney,
        EventDispatcherInterface $dispatcher
    ) {
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
        $this->exchangeMoney = $exchangeMoney;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(UpdateMoneyRates $message)
    {
        $now = Date::now();
        $rates = $this->exchangeMoney->getCurrencyRate($message->getPaarCurrencies());

        $moneyExchangeRates = [];
        foreach ($message->getPaarCurrencies() as $paarCurrency) {
            $rate = $this->exchangeMoneyRepository->findRateByPaarCurrencyDateAt($paarCurrency, $now);

            if ($rate === null) {
                list($fromCurrency, $toCurrency) = explode('_', $paarCurrency);
                $rate = (new Rate())
                    ->setFromCurrency(Currency::fromCode($fromCurrency))
                    ->setToCurrency(Currency::fromCode($toCurrency))
                    ->setPaarCurrency($paarCurrency)
                    ->setDateAt($now)
                ;
            }

            $rate->setRate($rates[$paarCurrency]);

            $this->exchangeMoneyRepository->saveRate($rate);

            $moneyExchangeRates[] = $rate;
        }

        $this->dispatcher->dispatch(
            new MoneyRatesUpdated(
                $moneyExchangeRates
            )
        );
    }
}
