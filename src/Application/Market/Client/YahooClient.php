<?php

namespace App\Application\Market\Client;

use App\Domain\Market\MarketData;
use App\Domain\Market\MarketPrice;
use App\Infrastructure\Context\Context;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use League\Csv\Reader;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function array_slice;
use function reset;
use function time;

final class YahooClient implements DividendsClientInterface, PricesClientInterface
{
    private const YAHOO_URI = 'https://query1.finance.yahoo.com/v7/finance/download/%s?period1=%d&period2=%d';
    private const YAHOO_DIVIDEND_QUERY = '&interval=1mo&events=div';
    private const YAHOO_PRICE_WEEKLY_QUERY = '&interval=1wk&events=history';
    private const YAHOO_PRICE_DAILY_QUERY = '&interval=1d&events=history';

    /**
     * @todo Move to service.yml as a parameters or enable a holiday system fo the stock market
     *
     * @var array
     */
    private $holidays = [

    ];

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getDividends(string $stock): array
    {
        $response = $this->getResponse(
            Context::TODO(),
            self::YAHOO_DIVIDEND_QUERY,
            $stock,
            new DateTimeImmutable('1996-08-13')
        );

        $body = $response->getContent();

        $reader = Reader::createFromString($body);
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        $dividends = iterator_to_array($reader->getRecords(['date', 'dividend']));

        return $dividends ?? [];
    }

    private function getResponse(Context $context, string $urlQuery, string $stock, DateTimeInterface $date): ResponseInterface
    {
        $url = sprintf(
            self::YAHOO_URI . $urlQuery,
            $stock,
            $date->getTimestamp(),
            time()
        );

        Logger::debug(
            $context->addKeysAndValues(
                [
                    'url' => $url,
                ]
            ),
            'requesting url'
        );
        return $this->client->request('GET', $url);
    }

    public function getHistoricalData(Context $context, Currency $currency, string $stock, ?DateTimeInterface $date): array
    {
        $daily = $this->getDailyPrices($context, $stock, $date);
        $weekly = $this->getWeeklyPrices($context, $stock, $date);

        return $this->hydratePrices($currency, $daily, $weekly);
    }

    private function getDailyPrices(Context $context, string $stock, ?DateTimeInterface $date): array
    {
        $context = $context->addKeysAndValues(
            [
                'func' => 'YahooClient.getDailyPrices'

            ]
        );

        $response = $this->getResponse(
            $context,
            self::YAHOO_PRICE_DAILY_QUERY,
            $stock,
            $date ?? new DateTimeImmutable('2014-12-29')
        );

        $body = $response->getContent();

        $reader = Reader::createFromString($body);
        $reader->setDelimiter(',');
        $reader->setHeaderOffset(0);
        $dailyPrices = iterator_to_array(
            $reader->getRecords(['Date', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume'])
        );

        return $dailyPrices ?? [];
    }

    private function getWeeklyPrices(Context $context, string $stock, ?DateTimeInterface $date): array
    {
        $context = $context->addKeysAndValues(
            [
                'func' => 'YahooClient.getWeeklyPrices'

            ]
        );

        $response = $this->getResponse(
            $context,
            self::YAHOO_PRICE_WEEKLY_QUERY,
            $stock,
            $date ?? new DateTimeImmutable('2015-01-01')
        );

        $body = $response->getContent();

        $reader = Reader::createFromString($body);
        $reader->setDelimiter(',');
        $reader->setHeaderOffset(0);
        $dailyWeekly = iterator_to_array(
            $reader->getRecords(['Date', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume'])
        );

        return $dailyWeekly ?? [];
    }

    private function hydratePrices(Currency $currency, array $daily, array $weekly)
    {
        $prices = [];

        $week = 1;
        $weekPrice = reset($weekly);
        $weekStartDate = new DateTimeImmutable($weekPrice['Date']);
        $week52Low = null;
        $week52High = null;

        /** @var MarketPrice $price */
        $price = null;
        $day = 0;

        foreach (array_slice($weekly, 1) as $nextWeekPrice) {
            $nextWeekStartDate = new DateTimeImmutable($nextWeekPrice['Date']);

            foreach (array_slice($daily, $day) as $dayPrice) {
                $date = new DateTimeImmutable($dayPrice['Date']);

                if ($date < $weekStartDate || $date >= $nextWeekStartDate) {
                    break;
                }

                $price = (new MarketData())
                    ->setDateAt(new DateTime($dayPrice['Date']))
                    ->setOpen(new Money($currency, $dayPrice['Open'] * 100))
                    ->setClose(new Money($currency, $dayPrice['Close'] * 100))
                    ->setDayHigh(new Money($currency, $dayPrice['High'] * 100))
                    ->setDayLow(new Money($currency, $dayPrice['Low'] * 100))
                    ->setWeekHigh(new Money($currency, $weekPrice['High'] * 100))
                    ->setWeekLow(new Money($currency, $weekPrice['Low'] * 100));

                $day++;
                $prices[] = $price;
            }

            $weekPrice = $nextWeekPrice;
            $weekStartDate = $nextWeekStartDate;
            $week++;
        }

        return $prices;
    }
}
