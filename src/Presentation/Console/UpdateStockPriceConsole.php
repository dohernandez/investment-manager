<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\LoadYahooQuote;
use App\Application\Market\Command\UpdateHistoricalStockPrice;
use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Market\Scraper\StockCrawled;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Infrastructure\Process\WaitGroup;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

use Symfony\Component\Process\Exception\ProcessFailedException;

use function sprintf;

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

    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        ProjectionStockRepositoryInterface $stockRepository,
        ProjectionWalletRepositoryInterface $walletRepository,
        MessageBusInterface $bus,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        parent::__construct($bus);

        $this->stockRepository = $stockRepository;
        $this->logger = $logger;
        $this->walletRepository = $walletRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        parent::configure();

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
            )
            ->addOption(
                'historical',
                null,
                InputOption::VALUE_OPTIONAL,
                'Update historical data instead',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (parent::execute($input, $output)) {
            return;
        }

        $threads = $input->getOption('threads');

        $stocks = [];
        if ($symbol = $input->getOption('symbol')) {
            if ($stock = $this->stockRepository->findBySymbol($symbol)) {
                $stocks[] = [
                    'id'            => $stock->getId(),
                    'symbol'        => $stock->getSymbol(),
                    'yahoo_symbol'  => $stock->getMetadata()->getYahooSymbol(),
                    'market_symbol' => $stock->getMarket()->getSymbol(),
                ];
            }
        } elseif ($wallet = $input->getOption('wallet')) {
            $wStocks = $this->walletRepository->findAllStocksInWalletOnOpenPositionBySlug($wallet);
            foreach ($wStocks as $stock) {
                $stocks[] = [
                    'id'            => $stock->getId(),
                    'symbol'        => $stock->getSymbol(),
                    'yahoo_symbol'  => $stock->getYahooSymbol(),
                    'market_symbol' => $stock->getMarket()->getSymbol(),
                ];
            }
        } else {
            $stocksListed = $this->stockRepository->findAllListed();
            foreach ($stocksListed as $stock) {
                $stocks[] = [
                    'id'            => $stock->getId(),
                    'symbol'        => $stock->getSymbol(),
                    'yahoo_symbol'  => $stock->getMetadata()->getYahooSymbol(),
                    'market_symbol' => $stock->getMarket()->getSymbol(),
                ];
            }
        }

        if ($skip = $input->getOption('skip')) {
            $stocks = \array_slice($stocks, $skip);
        }

        if ($limit = $input->getOption('limit')) {
            $stocks = \array_slice($stocks, 0, $limit);
        }

        $io->progressStart(count($stocks));
        $wg = new WaitGroup();
        $r = 0;

        foreach ($stocks as $stock) {
            $this->logger->debug(
                'Crawling stock',
                [
                    'stock_symbol'        => $stock['symbol'],
                    'stock_market_symbol' => $stock['market_symbol'],
                ]
            );

            $operation = 'processUpdateStockPrice';
            if ($this->isHistorical($input)) {
                $operation = 'processUpdateHistoricalStockPrice';
            }

            $this->process(
                self::$defaultName,
                $operation,
                [
                    $stock['id'],
                    $stock['symbol'],
                    $stock['yahoo_symbol'],
                    $stock['market_symbol'],
                ],
                $wg
            );

            $io->progressAdvance();

            $r++;
            if ($r === $threads) {
                $r = $wg->wait(1);
            }
        }

        $wg->wait();
        foreach ($wg->getFailed() as $error) {
            $io->error(
                sprintf(
                    'failed scraper update stock, error: %s',
                    (new ProcessFailedException($error))->getMessage()
                )
            );
        }
        $io->progressFinish();

        $io->success('Price updated successfully.');
    }

    /**
     * @param array $stock [0:id => ..., 1:symbol, 2:yahoo_symbol, 3:market_symbol]
     */
    protected function processUpdateStockPrice(array $stock): void
    {
        /** @var StockCrawled $crawled */
        $crawled = $this->handle(
            new LoadYahooQuote(
                $stock[1],
                $stock[2]
            )
        );

        $this->em->transactional(
            function () use ($stock, $crawled) {
                $this->handle(
                    new UpdateStockPrice(
                        $stock[0],
                        $crawled->getPrice(),
                        $crawled->getChangePrice(),
                        $crawled->getPreClose(),
                        $crawled->getData(),
                        $crawled->getPeRatio(),
                        $crawled->getWeek52Low(),
                        $crawled->getWeek52High()
                    )
                );
            }
        );
    }

    /**
     * @param array $stock [0:id => ..., 1:symbol, 2:yahoo_symbol, 3:market_symbol]
     */
    protected function processUpdateHistoricalStockPrice(array $stock): void
    {
        $this->em->transactional(
            function () use ($stock) {
                $this->handle(
                    new UpdateHistoricalStockPrice(
                        $stock[0]
                    )
                );
            }
        );
    }
}
