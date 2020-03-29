<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\LoadYahooStockMarketQuote;
use App\Application\Market\Command\UpdateStockMarketPrice;
use App\Application\Market\Scraper\StockCrawled;
use App\Infrastructure\Storage\Console\ConsoleStockMarketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateStockMarketPriceConsole extends Console
{
    protected static $defaultName = 'app:update-stock-market-price';

    /**
     * @var ConsoleStockMarketRepository
     */
    private $consoleStockMarketRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        ConsoleStockMarketRepository $consoleStockMarketRepository,
        MessageBusInterface $bus,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        parent::__construct($bus);

        $this->consoleStockMarketRepository = $consoleStockMarketRepository;
        $this->logger = $logger;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update all stock markets price value based on the yahoo website.')
            ->addOption('symbol', 's', InputOption::VALUE_OPTIONAL, 'Stock symbol');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $stockMarkets = [];
        if ($symbol = $input->getOption('symbol')) {
            if ($stockMarket = $this->consoleStockMarketRepository->findBySymbol($symbol)) {
                $stockMarkets[] = $stockMarket;
            }
        } else {
            $stockMarkets = $this->consoleStockMarketRepository->findAll();
        }

        $io->progressStart(count($stockMarkets));

        foreach ($stockMarkets as $stockMarket) {
            try {
                $this->logger->debug(
                    'Crawling stockMarket',
                    [
                        'stock_market_symbol'       => $stockMarket['symbol'],
                        'stock_market_currency'     => $stockMarket['currency'],
                        'stock_market_yahoo_symbol' => $stockMarket['yahoo_symbol'],
                    ]
                );

                /** @var StockCrawled $crawled */
                $crawled = $this->handle(
                    new LoadYahooStockMarketQuote(
                        $stockMarket['currency'],
                        $stockMarket['yahoo_symbol']
                    )
                );

                $this->em->transactional(
                    function () use ($stockMarket, $crawled) {
                        $this->handle(
                            new UpdateStockMarketPrice(
                                $stockMarket['id'],
                                $crawled->getValue(),
                                $crawled->getChangePrice(),
                                $crawled->getPreClose(),
                                $crawled->getOpen(),
                                $crawled->getDayLow(),
                                $crawled->getDayHigh(),
                                $crawled->getWeek52Low(),
                                $crawled->getWeek52High()
                            )
                        );
                    }
                );
            } catch (\Exception $e) {
                $io->error(
                    sprintf(
                        'failed scraper update stockMarket %s, error: %s',
                        $stockMarket['symbol'],
                        $e->getMessage()
                    )
                );
            } finally {
                $io->progressAdvance();
            }
        }

        $io->progressFinish();

        $io->success('Price updated successfully.');
    }
}
