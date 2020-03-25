<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\LoadYahooStockMarketQuote;
use App\Application\Market\Scraper\StockMarketScraperInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class LoadYahooStockMarketQuoteHandler implements MessageHandlerInterface
{
    /**
     * @var StockMarketScraperInterface
     */
    private $stockMarketScraper;

    public function __construct(StockMarketScraperInterface $stockMarketScraper)
    {
        $this->stockMarketScraper = $stockMarketScraper;
    }

    public function __invoke(LoadYahooStockMarketQuote $message)
    {
        return $this->stockMarketScraper->scrap($message->getCurrency(), $message->getSymbol());
    }
}
