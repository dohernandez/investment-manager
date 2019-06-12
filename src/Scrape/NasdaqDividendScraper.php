<?php

namespace App\Scrape;

use App\Entity\Stock;
use App\Entity\StockDividend;
use Goutte\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class NasdaqDividendScraper
{
    const NASDAQ_DIVIDEND_URI = 'https://www.nasdaq.com/symbol/%s/dividend-history';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        LoggerInterface $logger
    ) {

        $this->client = $client;
        $this->logger = $logger;
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

                try {
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
                } catch (\Exception $e) {
                    $this->logger->debug('Failed parsing row dividend', [
                        'exception' => $e->getMessage(),
                        'node' => $trNode,
                    ]);
                }
            });

        // Adding projected dividend if the last dividend ex date has passed.
        $nextDividend = $stock->nextDividend();

        if ($nextDividend === null) {
            $preDividend = $stock->preDividend();

            if ($preDividend !== null && $preDividend->getExDate() > new \DateTime('-3 months')) {
                $exDate = (clone $preDividend->getExDate())
                    ->add(new \DateInterval('P3M'));

                $nextDividend = new StockDividend();
                $nextDividend
                    ->setStatus(StockDividend::STATUS_PROJECTED)
                    ->setExDate($exDate)
                    ->setValue($preDividend->getValue());

                $stock->addDividend($nextDividend);

                $this->logger->debug('added next dividend', [
                    'symbol'        => $stock->getSymbol(),
                    'exDate' => $nextDividend->getExDate(),
                    'value' => $nextDividend->getValue(),
                    'status' => $nextDividend->getStatus(),
                ]);
            }
        }

        return $this;
    }
}
