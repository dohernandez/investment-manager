<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\LoadYahooQuote;
use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Market\Scraper\StockCrawled;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateStockPriceConsole extends Console
{
    protected static $defaultName = 'app:update-stock-price';

    /**
     * @var ProjectionStockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ProjectionStockRepositoryInterface $stockRepository,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        parent::__construct($bus);

        $this->stockRepository = $stockRepository;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update all stocks price value based on the yahoo website.')
            ->addOption('symbol', 's', InputOption::VALUE_OPTIONAL, 'Stock symbol')
            ->addOption('wallet', 'w', InputOption::VALUE_OPTIONAL, 'Wallet slug')
            ->addOption('skip', 'k', InputOption::VALUE_OPTIONAL, 'Skip first number of stocks from the resulting list')
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit first number of stocks from the resulting list'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $stocks = [];
        if ($symbol = $input->getOption('symbol')) {
            if ($stock = $this->stockRepository->findBySymbol($symbol)) {
                $stocks[] = $stock;
            }
        } elseif ($wallet = $input->getOption('wallet')) {
            // implement proxy pattern https://sourcemaking.com/design_patterns/proxy
//            $stocks = $this->walletRepository->findOneBySlug($wallet)->getPositions('open')->map(function ($position){
//                /** @var Position $position */
//                return $position->getStock();
//            });
        } else {
            $stocks = $this->stockRepository->findAllListed();
        }

        if ($skip = $input->getOption('skip')) {
            $stocks = \array_slice($stocks, $skip);
        }

        if ($limit = $input->getOption('limit')) {
            $stocks = \array_slice($stocks, 0, $limit);
        }

        $io->progressStart(count($stocks));

        foreach ($stocks as $stock) {
            try {
                $this->logger->debug(
                    'Crawling stock',
                    [
                        'stock_symbol' => $stock->getSymbol(),
                        'stock_market_symbol' => $stock->getMarket()->getSymbol(),
                    ]
                );

                /** @var StockCrawled $crawled */
                $crawled = $this->handle(
                    new LoadYahooQuote(
                        $stock->getSymbol(),
                        $stock->getMetadata()->getYahooSymbol()
                    )
                );

                $this->handle(
                    new UpdateStockPrice(
                        $stock->getId(),
                        $crawled->getValue(),
                        $crawled->getChangePrice(),
                        $crawled->getPreClose(),
                        $crawled->getOpen(),
                        $crawled->getPeRatio(),
                        $crawled->getDayLow(),
                        $crawled->getDayHigh(),
                        $crawled->getWeek52Low(),
                        $crawled->getWeek52High()
                    )
                );
            } catch (\Exception $e) {
                $io->error(
                    sprintf(
                        'failed scraper update stock %s, error: %s',
                        $stock->getSymbol(),
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
