<?php

namespace App\Application\ExchangeMoney\Handler;

use App\Application\ExchangeMoney\Command\UpdateMoneyRatesHistorical;
use App\Application\ExchangeMoney\Exchange\ExchangeMoneyInterface;
use App\Application\ExchangeMoney\Repository\ExchangeMoneyRepositoryInterface;
use App\Domain\ExchangeMoney\Rate;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Money\Currency;
use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use function array_merge_recursive;
use function explode;

final class UpdateMoneyRatesHistoricalHandler implements MessageHandlerInterface
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

    public function __invoke(UpdateMoneyRatesHistorical $message)
    {
        $now = Date::now();
        $beggingYear = Date::getDateTimeBeginYear(Date::getYear($now));

        $historicalRates = [];
        while ($beggingYear < $now) {
            $historicalRates = array_merge_recursive(
                $historicalRates,
                $this->exchangeMoney->getCurrencyRateHistorical(
                    $message->getPaarCurrencies(),
                    null,
                    $now
                )
            );

            $now = Date::dayAgo(9, $now);
        }

        $moneyExchangeRates = [];
        foreach ($message->getPaarCurrencies() as $paarCurrency) {
            foreach ($historicalRates[$paarCurrency] as $date => $value) {
                $date = new DateTime($date);

                $rate = $this->exchangeMoneyRepository->findRateByPaarCurrencyDateAt($paarCurrency, $date);

                if ($rate === null) {
                    list($fromCurrency, $toCurrency) = explode('_', $paarCurrency);
                    $rate = (new Rate())
                        ->setFromCurrency(Currency::fromCode($fromCurrency))
                        ->setToCurrency(Currency::fromCode($toCurrency))
                        ->setPaarCurrency($paarCurrency)
                        ->setDateAt($date)
                    ;
                }

                $rate->setRate($value);
                $this->exchangeMoneyRepository->saveRate($rate);

                $moneyExchangeRates[] = $rate;
            }

        }
    }
}
