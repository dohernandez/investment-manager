<?php

namespace App\Scrape;

use App\Entity\Stock;
use App\Entity\StockInfo;
use App\Repository\StockInfoRepository;
use App\Repository\StockMarketRepository;
use App\VO\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class YahooStockScraper
{
    const YAHOO_FINANCE_QUOTE_URI = 'https://finance.yahoo.com/quote';

    const SELECTORS = [
        'quote_change' => 'div#quote-header-info',
        'quote_summary' => 'div#quote-summary',
        'profile' => 'div#Col1-3-Profile-Proxy',
    ];

    const REACT_ID = [
        'value' => '14',
        'change' => '16',
    ];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var StockInfoRepository
     */
    private $stockInfoRepository;

    /**
     * @var StockMarketRepository
     */
    private $stockMarketRepository;

    public function __construct(
        Client $client,
        StockInfoRepository $stockInfoRepository,
        StockMarketRepository $stockMarketRepository
    ) {
        $this->client = $client;
        $this->stockInfoRepository = $stockInfoRepository;
        $this->stockMarketRepository = $stockMarketRepository;
    }

    /**
     * Scrape yahoo page to find current price and update the stock.
     *
     * @param Stock $stock
     *
     * @return YahooStockScraper
     */
    public function updateFromQuote(Stock $stock): self
    {
        $crawler = $this->client->request('GET', sprintf('%1$s/%2$s?p=%2$s', self::YAHOO_FINANCE_QUOTE_URI, $stock->getSymbol()));

        $quoteHeaderInfoNode = $crawler->filter(self::SELECTORS['quote_change'])->getNode(0);

        if (empty($stock->getName())) {
            // update name
            (new Crawler($quoteHeaderInfoNode->childNodes->item(1)->childNodes->item(0)))
                ->filter('h1')
                ->each(function ($node) use ($stock) {
                    /** @var Crawler $node */

                    if (preg_match('/^.* - (.*)/', $node->extract('_text')[0], $matches) !== false) {
                        $stock->setName($matches[1]);
                    }
                });

        }

        if (empty($stock->getMarket())) {
            // update market
            (new Crawler($quoteHeaderInfoNode->childNodes->item(1)->childNodes->item(0)))
                ->filter('span')
                ->each(function ($node) use ($stock) {
                    /** @var Crawler $node */

                    if (preg_match('/^(.*) - .*/', $node->extract('_text')[0], $matches) !== false) {

                        $market = $this->stockMarketRepository->findOneBy([
                            'yahoo_symbol' => $matches[1]
                        ]);

                        $stock->setMarket($market);
                    }
                });

        }

        // update preClose, open, peRatio, dayLow and dayHigh, Week52Low and Week52High
        (new Crawler($quoteHeaderInfoNode->childNodes->item(2)))
            ->filter('span')
            ->each(function ($node) use ($stock) {
                /** @var Crawler $node */

                $reactId = $node->extract('data-reactid')[0];
                if ($reactId == '14') {
                    $stock->setValue(Money::fromUSDValue($this->parserPrice($node->extract('_text')[0])));
                }

                if ($reactId == '16') {
                    if (preg_match('/^(.*) .*/', $node->extract('_text')[0], $matches) !== false) {
                        $stock->setLastChangePrice(Money::fromUSDValue($this->parserPrice($matches[1])));
                    }
                }

            });

        // update preClose, open, peRatio, dayLow and dayHigh, Week52Low and Week52High
        $crawler->filter(self::SELECTORS['quote_summary']. ' tr')
            ->each(function ($node) use ($stock) {
                /** @var Crawler $node */

                $tdNodes = $node->filter('td');

                if ($tdNodes->eq(0)->extract('_text')[0] == 'Previous Close') {
                    $stock->setPreClose(Money::fromUSDValue($this->parserPrice($tdNodes->eq(1)->extract('_text')[0])));
                }

                if ($tdNodes->eq(0)->extract('_text')[0] == 'Open') {
                    $stock->setOpen(Money::fromUSDValue($this->parserPrice($tdNodes->eq(1)->extract('_text')[0])));
                }

                if ($tdNodes->eq(0)->extract('_text')[0] == 'PE Ratio (TTM)') {
                    $stock->setPeRatio(floatval($tdNodes->eq(1)->extract('_text')[0]));
                }

                if ($tdNodes->eq(0)->extract('_text')[0] == 'Day\'s Range') {
                    if (preg_match('/^(.*) - (.*)$/', $tdNodes->eq(1)->extract('_text')[0], $matches) !== false) {
                        $stock->setDayLow(Money::fromUSDValue($this->parserPrice($matches[1])));
                        $stock->setDayHigh(Money::fromUSDValue($this->parserPrice($matches[2])));
                    }
                }

                if ($tdNodes->eq(0)->extract('_text')[0] == '52 Week Range') {
                    if (preg_match('/^(.*) - (.*)$/', $tdNodes->eq(1)->extract('_text')[0], $matches) !== false) {
                        $stock->setWeek52Low(Money::fromUSDValue($this->parserPrice($matches[1])));
                        $stock->setWeek52High(Money::fromUSDValue($this->parserPrice($matches[2])));
                    }
                }
            });

        return $this;
    }

    private function parserPrice(string $price, int $divisor = 100): int
    {
        $price = str_replace(',', '.', $price);

        return floatval($price) * $divisor;
    }

    /**
     * Scrape yahoo page to find stock description along with the sector and the industry,
     * and update the stock.
     *
     * @param Stock $stock
     * @param ArrayCollection|null $stockInfos
     *
     * @return YahooStockScraper
     */
    public function updateFromProfile(Stock $stock, ?ArrayCollection $stockInfos = null): self
    {
        $crawler = $this->client->request('GET', sprintf('%1$s/%2$s/profile?p=%2$s', self::YAHOO_FINANCE_QUOTE_URI, $stock->getSymbol()));

        $profileNode = $crawler->filter(self::SELECTORS['profile'])->eq(0);

        $descNode = $profileNode->filter('section.quote-sub-section p')->eq(0);
        $stock->setDescription($descNode->extract('_text')[0]);

        if ($stock->getSector() !== null && $stock->getIndustry() !== null) {
            return  $this;
        }

        $infoNodes = $profileNode->filter('div.asset-profile-container p span');
        for ($i = 0; $i < $infoNodes->count(); $i += 2) {

            if ($stock->getSector() === null && $infoNodes->eq($i)->extract('_text')[0] == 'Sector') {
                $stockInfo = $this->findOrCreateStockInfo(
                    StockInfo::SECTOR,
                    strtoupper($infoNodes->eq($i+1)->extract('_text')[0]),
                    $stockInfos
                );
                $stock->setSector($stockInfo);

                continue;
            }

            if ($stock->getIndustry() === null && $infoNodes->eq($i)->extract('_text')[0] == 'Industry') {
                $stockInfo = $this->findOrCreateStockInfo(
                    StockInfo::INDUSTRY,
                    strtoupper($infoNodes->eq($i+1)->extract('_text')[0]),
                    $stockInfos
                );
                $stock->setIndustry($stockInfo);

                continue;
            }
        }

        return $this;
    }

    protected function findOrCreateStockInfo(string $type, string $name, ?ArrayCollection $stockInfos): StockInfo
    {
        $stockInfo = null;

        if ($stockInfos) {
            $stockInfo = $stockInfos->get($type.'.'.$name);
        }

        if ($stockInfo === null) {
            $stockInfo = $this->stockInfoRepository->findOneBy([
                'type' => $type,
                'name' => $name
            ]);
        }

        if ($stockInfo === null) {
            $stockInfo = new StockInfo();
            $stockInfo->setType($type)
                ->setName($name)
            ;
        }

        if ($stockInfos) {
            $stockInfos->set($type . '.' . $name, $stockInfo);
        }

        return $stockInfo;
    }
}
