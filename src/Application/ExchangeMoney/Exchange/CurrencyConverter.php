<?php

namespace App\Application\ExchangeMoney\Exchange;

use App\Infrastructure\Date\Date;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function array_chunk;
use function array_merge;

final class CurrencyConverter implements ExchangeMoneyInterface
{
    private const CURRENCY_CONVERTER_URI = 'https://free.currencyconverterapi.com/api/v6/convert?q=%s&compact=ultra&apiKey=%s';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $apiKey, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function getCurrencyRate(array $paarCurrencies): array
    {
        $currencyRates = [];
        foreach (array_chunk($paarCurrencies, 2) as $item) {
            $response = $this->httpClient->request(
                'GET',
                sprintf(self::CURRENCY_CONVERTER_URI, implode(',', $item), $this->apiKey)
            );

            $currencyRates = array_merge($currencyRates, $response->toArray());
        }

        return $currencyRates;
    }

    public function getCurrencyRateHistorical(
        array $paarCurrencies,
        DateTimeInterface $startDate = null,
        DateTimeInterface $endDate = null
    ): array {
        $endDate = $endDate ?? Date::now();
        $startDate = $startDate ?? Date::dayAgo(8, $endDate);

        $currencyRates = [];
        foreach (array_chunk($paarCurrencies, 2) as $item) {
            $response = $this->httpClient->request(
                'GET',
                sprintf(
                    self::CURRENCY_CONVERTER_URI . '&date=%s&endDate=%s',
                    implode(',', $item),
                    $this->apiKey,
                    $startDate->format(Date::FORMAT_ENGLISH),
                    $endDate->format(Date::FORMAT_ENGLISH)
                )
            );

            $currencyRates = array_merge($currencyRates, $response->toArray());
        }

        return $currencyRates;
    }
}
