<?php

namespace App\Application\Market\Client;

use League\Csv\Reader;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function time;

class YahooDividendsClient implements DividendsClientInterface
{
    private const YAHOO_DIVIDEND_URI = 'https://query1.finance.yahoo.com/v7/finance/download/%s?period1=%d&period2=%d&interval=1mo&events=div';

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
            sprintf(
                self::YAHOO_DIVIDEND_URI,
                $stock,
                (new \Datetime('1996-08-13'))->getTimestamp(),
                time()
            )
        );

        $body = $response->getContent();


        $reader = Reader::createFromString($body);
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        $dividends = iterator_to_array($reader->getRecords(['date', 'dividend']));
        \dump($body, $dividends);

        return $dividends ?? [];
    }
}
