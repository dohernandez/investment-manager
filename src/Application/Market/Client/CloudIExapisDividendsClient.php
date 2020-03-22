<?php

namespace App\Application\Market\Client;

use League\Csv\Reader;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function reset;
use function str_replace;
use function time;

class CloudIExapisDividendsClient implements DividendsClientInterface
{
    private const CLOUD_IEXAPIS_DIVIDEND_URI = 'https://cloud.iexapis.com/stable/stock/%s/dividends/%s?token=%s';

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $token;

    public function __construct(HttpClientInterface $client, string $token)
    {
        $this->client = $client;
        $this->token = $token;
    }

    public function getDividends(string $stock): array
    {
        $stock = str_replace('.TO', '', $stock);

        $dividends = [];

        $response = $this->getResponse($stock, '1m');
        $body = $response->toArray();
        if (!empty($body)) {
            $dividends[] = reset($body);
        }

        $response = $this->getResponse($stock, 'next');
        $body = $response->toArray();
        $dividends[] = $body;

        return $dividends ?? [];
    }

    private function getResponse(string $stock, string $range = 'next')
    {
        return $this->client->request(
            'GET',
            sprintf(
                self::CLOUD_IEXAPIS_DIVIDEND_URI,
                $stock,
                $range,
                $this->token
            )
        );
    }
}
