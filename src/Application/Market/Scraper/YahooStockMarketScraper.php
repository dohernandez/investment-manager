<?php

namespace App\Application\Market\Scraper;

use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Domain\Market\MarketData;
use App\Domain\Market\StockInfo;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Exception\NotFoundException;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

final class YahooStockMarketScraper implements StockMarketScraperInterface
{
    private const YAHOO_FINANCE_QUOTE_URI = 'https://finance.yahoo.com/quote';

    private const SELECTORS = [
        'quote_change'  => 'div#quote-header-info',
        'quote_summary' => 'div#quote-summary',
    ];

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function scrap(Currency $currency, string $symbol): StockCrawled
    {
        $stockCrawled = (new StockCrawled($symbol))
            ->setCurrency($currency)
            ->setData(
                (new MarketData())->setDateAt(Date::now())
            );

        $this->updateFromQuote($stockCrawled);

        return $stockCrawled;
    }

    /**
     * Scrape yahoo page to find current price and update the stock.
     *
     * @param StockCrawled $stockCrawled
     *
     * @return $this
     */
    private function updateFromQuote(StockCrawled $stockCrawled): self
    {
        $crawler = $this->client->request(
            'GET',
            sprintf(
                '%1$s/%2$s?p=%2$s',
                self::YAHOO_FINANCE_QUOTE_URI,
                $stockCrawled->getYahooSymbol() ?? $stockCrawled->getSymbol()
            )
        );

        $this->updateCurrentPrice($crawler, $stockCrawled)
            ->updatePriceDetails($crawler, $stockCrawled);

        // the client is restarted in order to avoid ban from the website scrapped.
        // this is in case the client is reuse more than one time, for example in command line
        // during updating stocks price.
        $this->client->restart();

        return $this;
    }

    /**
     * Update current price and last change price.
     *
     * @param Crawler $crawler
     * @param StockCrawled $stockCrawled
     *
     * @return $this
     */
    private function updateCurrentPrice(Crawler $crawler, StockCrawled $stockCrawled): self
    {
        $currency = $stockCrawled->getCurrency();

        $node = $crawler->filter(self::SELECTORS['quote_change'])->getNode(0);

        (new Crawler($node->childNodes->item(2)))
            ->filter('span')
            ->each(
                function ($node) use ($stockCrawled, $currency) {
                    /** @var Crawler $node */

                    $reactId = $node->extract('data-reactid')[0];
                    if ($reactId == '14') {
                        $stockCrawled->setPrice(new Money($currency, Money::parser($node->extract('_text')[0])));
                    }

                    if ($reactId == '16') {
                        if (preg_match('/^(.*) .*/', $node->extract('_text')[0], $matches) !== false) {
                            $stockCrawled->setChangePrice(new Money($currency, Money::parser($matches[1])));
                        }
                    }
                }
            );

        return $this;
    }

    /**
     * Update preClose, open, peRatio, dayLow and dayHigh, Week52Low and Week52High
     *
     * @param Crawler $crawler
     * @param StockCrawled $stockCrawled
     *
     * @return $this
     */
    private function updatePriceDetails(Crawler $crawler, StockCrawled $stockCrawled): self
    {
        $currency = $stockCrawled->getCurrency();

        // update preClose, open, peRatio, dayLow and dayHigh, Week52Low and Week52High
        $crawler->filter(self::SELECTORS['quote_summary'] . ' tr')
            ->each(
                function ($node) use ($stockCrawled, $currency) {
                    /** @var Crawler $node */

                    $tdNodes = $node->filter('td');

                    if ($tdNodes->eq(0)->extract('_text')[0] == 'Previous Close') {
                        $stockCrawled->setPreClose(
                            new Money($currency, Money::parser($tdNodes->eq(1)->extract('_text')[0]))
                        );
                    }

                    if ($tdNodes->eq(0)->extract('_text')[0] == 'Open') {
                        $stockCrawled->getData()->setOpen(
                            new Money($currency, Money::parser($tdNodes->eq(1)->extract('_text')[0]))
                        );
                    }

                    if ($tdNodes->eq(0)->extract('_text')[0] == 'Day\'s Range') {
                        if (preg_match('/^(.*) - (.*)$/', $tdNodes->eq(1)->extract('_text')[0], $matches) !== false) {
                            $stockCrawled->getData()->setDayLow(new Money($currency, Money::parser($matches[1])));
                            $stockCrawled->getData()->setDayHigh(new Money($currency, Money::parser($matches[2])));
                        }
                    }

                    if ($tdNodes->eq(0)->extract('_text')[0] == '52 Week Range') {
                        if (preg_match('/^(.*) - (.*)$/', $tdNodes->eq(1)->extract('_text')[0], $matches) !== false) {
                            $stockCrawled->setWeek52Low(new Money($currency, Money::parser($matches[1])));
                            $stockCrawled->setWeek52High(new Money($currency, Money::parser($matches[2])));
                        }
                    }
                }
            );

        return $this;
    }
}
