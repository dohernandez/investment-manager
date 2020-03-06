<?php

namespace App\Application\ExchangeMoney\Exchange;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CurrencyConverter implements ExchangeMoneyInterface
{
    private const CURRENCY_CONVERTER_URI = 'http://free.currencyconverterapi.com/api/v6/convert?q=%s&compact=ultra&apiKey=%s';

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
        $response = $this->httpClient->request(
            'GET',
            sprintf(self::CURRENCY_CONVERTER_URI, implode(',', $paarCurrencies), $this->apiKey)
        );

        return $response->toArray();
    }
}
