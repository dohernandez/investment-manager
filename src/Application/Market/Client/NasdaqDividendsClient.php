<?php

namespace App\Application\Market\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class NasdaqDividendsClient implements DividendsClientInterface
{
    private const NASDAQ_DIVIDEND_URI = 'https://api.nasdaq.com/api/quote/%s/dividends?assetclass=stocks';

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
        $response = $this->client->request(
            'GET',
            sprintf(self::NASDAQ_DIVIDEND_URI, $stock)
        );

        $body = $response->toArray();

        $dividends = $body['data']['dividends']['rows'];

        return $dividends ?? [];
    }
}
