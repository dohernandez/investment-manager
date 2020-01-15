<?php

namespace App\Application\Market\Scraper;

use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Domain\Market\StockInfo;
use App\Infrastructure\Money\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

final class YahooStockScraper implements StockScraperInterface
{
    private const YAHOO_FINANCE_QUOTE_URI = 'https://finance.yahoo.com/quote';

    private const SELECTORS = [
        'quote_change'  => 'div#quote-header-info',
        'quote_summary' => 'div#quote-summary',
        'profile'       => 'div#Col1-3-Profile-Proxy',
    ];

    /**
     * @var ArrayCollection
     */
    private $stockInfos;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ProjectionStockMarketRepositoryInterface
     */
    private $projectionStockMarketRepository;

    /**
     * @var ProjectionStockInfoRepositoryInterface
     */
    private $projectionStockInfoRepository;

    public function __construct(
        Client $client,
        ProjectionStockMarketRepositoryInterface $projectionStockMarketRepository,
        ProjectionStockInfoRepositoryInterface $projectionStockInfoRepository
    ) {
        $this->client = $client;
        $this->projectionStockMarketRepository = $projectionStockMarketRepository;

        $this->stockInfos = new ArrayCollection();
        $this->projectionStockInfoRepository = $projectionStockInfoRepository;
    }

    public function scrap(string $symbol, ?string $yahooSymbol = null): StockCrawled
    {
        $stockCrawled = new StockCrawled($symbol, $yahooSymbol);

        $this->updateFromQuote($stockCrawled)
            ->updateFromProfile($stockCrawled);

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

        $this->updateName($crawler, $stockCrawled)
            ->updateMarket($crawler, $stockCrawled)
            ->updateCurrentPrice($crawler, $stockCrawled)
            ->updatePriceDetails($crawler, $stockCrawled);

        return $this;
    }

    private function updateName(Crawler $crawler, StockCrawled $stockCrawled): self
    {
        if (empty($stockCrawled->getName())) {
            $node = $crawler->filter(self::SELECTORS['quote_change'])->getNode(0);

            // update name
            (new Crawler($node->childNodes->item(1)->childNodes->item(0)))
                ->filter('h1')
                ->each(
                    function ($node) use ($stockCrawled) {
                        /** @var Crawler $node */

                        if (preg_match('/^.* - (.*)/', $node->extract('_text')[0], $matches) !== false) {
                            $stockCrawled->setName($matches[1]);
                        }
                    }
                );
        }

        return $this;
    }

    private function updateMarket(Crawler $crawler, StockCrawled $stockCrawled): self
    {
        if (empty($stockCrawled->getMarket())) {
            $node = $crawler->filter(self::SELECTORS['quote_change'])->getNode(0);

            // update market
            (new Crawler($node->childNodes->item(1)->childNodes->item(0)))
                ->filter('span')
                ->each(
                    function ($node) use ($stockCrawled) {
                        /** @var Crawler $node */

                        if (preg_match('/^(.*) - .*/', $node->extract('_text')[0], $matches) !== false) {
                            $market = $this->projectionStockMarketRepository->findBySymbol($matches[1]);

                            $stockCrawled->setMarket($market);
                        }
                    }
                );
        }

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
                        $stockCrawled->setValue(new Money($currency, Money::parser($node->extract('_text')[0])));
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
                        $stockCrawled->setOpen(
                            new Money($currency, Money::parser($tdNodes->eq(1)->extract('_text')[0]))
                        );
                    }

                    if ($tdNodes->eq(0)->extract('_text')[0] == 'PE Ratio (TTM)') {
                        $stockCrawled->setPeRatio(floatval($tdNodes->eq(1)->extract('_text')[0]));
                    }

                    if ($tdNodes->eq(0)->extract('_text')[0] == 'Day\'s Range') {
                        if (preg_match('/^(.*) - (.*)$/', $tdNodes->eq(1)->extract('_text')[0], $matches) !== false) {
                            $stockCrawled->setDayLow(new Money($currency, Money::parser($matches[1])));
                            $stockCrawled->setDayHigh(new Money($currency, Money::parser($matches[2])));
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

    /**
     * Scrape yahoo page to find stock description along with the sector and the industry,
     * and update the stock.
     *
     * @param StockCrawled $stockCrawled
     *
     * @return $this
     */
    private function updateFromProfile(StockCrawled $stockCrawled): self
    {
        $crawler = $this->client->request(
            'GET',
            sprintf(
                '%1$s/%2$s/profile?p=%2$s',
                self::YAHOO_FINANCE_QUOTE_URI,
                $stockCrawled->getYahooSymbol() ?? $stockCrawled->getSymbol()
            )
        );

        $crawler = $crawler->filter(self::SELECTORS['profile'])->eq(0);

        $this->updateDescription($crawler, $stockCrawled)
            ->updateInfo($crawler, $stockCrawled);

        return $this;
    }

    private function updateDescription(Crawler $crawler, StockCrawled $stockCrawled): self
    {
        $descNode = $crawler->filter('section.quote-sub-section p')->eq(0);
        $stockCrawled->setDescription($descNode->extract('_text')[0]);

        return $this;
    }

    private function updateInfo(Crawler $crawler, StockCrawled $stockCrawled): self
    {
        $nodes = $crawler->filter('div.asset-profile-container p span');

        for ($i = 0; $i < $nodes->count(); $i += 2) {
            if ($nodes->eq($i)->extract('_text')[0] == 'Sector') {
                $name = strtoupper($nodes->eq($i + 1)->extract('_text')[0]);

                if ($stockCrawled->getSector() === null || $stockCrawled->getSector()->getName() != $name) {
                    $stockInfo = $this->projectionStockInfoRepository->findByName($name);

                    $stockCrawled->setSector($stockInfo ?? StockInfo::createTemporary($name, StockInfo::SECTOR));
                }

                continue;
            }

            if ($nodes->eq($i)->extract('_text')[0] == 'Industry') {
                $name = strtoupper($nodes->eq($i + 1)->extract('_text')[0]);

                if ($stockCrawled->getIndustry() === null || $stockCrawled->getIndustry()->getName() != $name) {
                    $stockInfo = $this->projectionStockInfoRepository->findByName($name);

                    $stockCrawled->setIndustry($stockInfo ?? StockInfo::createTemporary($name, StockInfo::INDUSTRY));
                }

                continue;
            }
        }

        return $this;
    }
}
