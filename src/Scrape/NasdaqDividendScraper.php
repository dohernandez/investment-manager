<?php

namespace App\Scrape;

use App\Entity\Stock;
use App\Entity\StockDividend;
use App\VO\Money;
use Doctrine\Common\Collections\Collection;
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

    /**
     * Update the stock dividends.
     * Remove all the dividends that aren't payed yet, and update again
     * the list until the end of the year.
     *
     * @param Stock $stock
     *
     * @return NasdaqDividendScraper
     * @throws \Exception
     */
    public function updateHistoricalDividend(Stock $stock): self
    {
        // Removing projected and announced dividends
        foreach ($stock->getProjectedAndAnnouncedDividends() as $stockDividend) {
            $stock->removeDividend($stockDividend);
        }

        $crawler = $this->client->request(
            'GET',
            sprintf(self::NASDAQ_DIVIDEND_URI, strtolower($stock->getSymbol()))
        );

        /** @var StockDividend $lastDividend */
        $lastDividend = null;

        $crawler->filter('table#quotes_content_left_dividendhistoryGrid tbody tr')
            ->each(function ($trNode) use ($stock, &$lastDividend) {
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
                    $stockDividend->setValue(Money::fromUSDValue(floatval(
                        $spanNodes->eq(1)->extract('_text')[0]
                    )));

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

                    if (!$lastDividend || $lastDividend->getExDate() < $stockDividend->getExDate()) {
                        $lastDividend = $stockDividend;
                    }
                } catch (\Exception $e) {
                    $this->logger->debug('Failed parsing row dividend', [
                        'exception' => $e->getMessage(),
                    ]);
                }
            });

        if ($lastDividend !== null) {
            // Adding projected dividend until the end of the year.
            $now = new \DateTimeImmutable();
            $year = $now->add(\DateInterval::createFromDateString('1 year'));

            if ($lastDividend->getExDate() > $now->add(\DateInterval::createFromDateString('-3 months'))) {
                $exDate = (clone $lastDividend->getExDate())
                    ->add(\DateInterval::createFromDateString('3 months'));

                while ($year >= $exDate) {
                    $nextDividend = new StockDividend();
                    $nextDividend
                        ->setStatus(StockDividend::STATUS_PROJECTED)
                        ->setExDate($exDate)
                        ->setValue(clone $lastDividend->getValue());

                    $stock->addDividend($nextDividend);

                    $exDate = (clone $exDate)
                        ->add(\DateInterval::createFromDateString('3 months'));
                }
            }
        }

        return $this;
    }
}
