<?php

namespace App\Client;

use App\Entity\Exchange;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyConverter
{
    const CURRENCY_CONVERTER_URI = 'http://free.currencyconverterapi.com/api/v6/convert?q=%s&compact=ultra&apiKey=%s';

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

    public function __construct(
        LoggerInterface $logger
    ) {

        // This solution is because I could not make work autowiring like describe the documentation
        // @see https://symfony.com/doc/current/components/http_client.html#symfony-framework-integration
        $this->httpClient = HttpClient::create();
        $this->logger = $logger;
        $this->apiKey = '1fc6722fc3459102ed99';
    }

    /**
     * @param Exchange[] $exchanges
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function updateRates(array $exchanges)
    {
        $convert = [];
        foreach ($exchanges as $exchange) {
            $convert[] = $exchange->getFromCurrency()->getCurrencyCode() . '_' . $exchange->getToCurrency()->getCurrencyCode();
        }

        $response = $this->httpClient->request(
            'GET',
            sprintf(self::CURRENCY_CONVERTER_URI, implode(',', $convert), $this->apiKey)
        );

        $rates = $response->toArray();

        foreach ($exchanges as $key => $exchange) {
            $rateKey = $exchange->getFromCurrency()->getCurrencyCode() . '_' . $exchange->getToCurrency()->getCurrencyCode();

            if (isset($rateKey)) {
                $exchange->setRate($rates[$rateKey]);
            }

            $exchanges[$key] = $exchange;
        }

    }
}
