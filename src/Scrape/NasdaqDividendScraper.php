<?php

namespace App\Scrape;

use App\Entity\Stock;
use App\Entity\StockDividend;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class NasdaqDividendScraper
{
    const NASDAQ_DIVIDEND_URI = 'https://www.nasdaq.com/symbol/%s/dividend-history';

    /**
     * @var Client
     */
    private $client;

    public function __construct(
        Client $client
    ) {

        $this->client = $client;
    }

    public function updateHistoricalDividend(Stock $stock): self
    {
        $crawler = $this->client->request(
            'GET',
            sprintf(self::NASDAQ_DIVIDEND_URI, strtolower($stock->getSymbol()))
        );

        $crawler->filter('table#quotes_content_left_dividendhistoryGrid tbody tr')
            ->each(function ($trNode) use ($stock) {
                /** @var Crawler $trNode */
                $spanNodes = $trNode->filter('span');

                $stockDividend = new StockDividend();
                $stockDividend->setStatus(StockDividend::STATUS_ANNOUNCED);

                // Ex/Eff Date
                $stockDividend->setExDate(new \DateTimeImmutable(
                    $spanNodes->eq(0)->extract('_text')[0]
                ));

                // Cash Amount
                $stockDividend->setValue(floatval(
                    $spanNodes->eq(1)->extract('_text')[0]
                ));

                // Record Date
                $stockDividend->setRecordDate(new \DateTimeImmutable(
                    $spanNodes->eq(3)->extract('_text')[0]
                ));

                // Payment Date
                $stockDividend->setPaymentDate(new \DateTimeImmutable(
                    $spanNodes->eq(4)->extract('_text')[0]
                ));

                $now = new \DateTimeImmutable();
                if ($stockDividend->getPaymentDate() < $now) {
                    $stockDividend->setStatus(StockDividend::STATUS_PAYED);
                }

                $stock->addDividend($stockDividend);
            });

        return $this;
    }
}
