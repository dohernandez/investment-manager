<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\LoadYahooQuote;
use App\Application\Market\Scraper\StockScraperInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class LoadYahooQuoteHandler implements MessageHandlerInterface
{
    /**
     * @var StockScraperInterface
     */
    private $stockScraper;

    public function __construct(StockScraperInterface $stockScraper)
    {
        $this->stockScraper = $stockScraper;
    }

    public function __invoke(LoadYahooQuote $message)
    {
        return $this->stockScraper->scrap($message->getSymbol(), $message->getYahooSymbol());
    }
}
