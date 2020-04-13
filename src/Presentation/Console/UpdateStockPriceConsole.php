<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\LoadYahooQuote;
use App\Application\Market\Command\UpdateHistoricalStockPrice;
use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Market\Scraper\StockCrawled;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

use function explode;
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
            )
            ->addOption(
                'stock',
                null,
                InputOption::VALUE_OPTIONAL,
                'Stock in a format id:symbol:yahoo_symbol:market:symbol.
                This option is mainly use by re-triggering the  command to avoid memory leak. Nevertheless it can
                be use by user with advance knowledge.
                For example: -stock 5e5ee0d4a3981:IRBT:NASDAQ'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $singleStock = false;
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

            $singleStock = true;
        } elseif ($stock = $input->getOption('stock')) {
            list($stockId, $symbol, $yahooSymbol, $marketSymbol) = explode(':', $stock);
            $stocks[] = [
                'id'            => $stockId,
                'symbol'        => $symbol,
                'yahoo_symbol'  => $yahooSymbol,
                'market_symbol' => $marketSymbol,
            ];

            $singleStock = true;
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

        foreach ($stocks as $stock) {
            try {
                $this->logger->debug(
                    'Crawling stock',
                    [
                        'stock_symbol'        => $stock['symbol'],
                        'stock_market_symbol' => $stock['market_symbol'],
                    ]
                );

                if (!$this->isHistorical($input)) {
                    /** @var StockCrawled $crawled */
                    $crawled = $this->handle(
                        new LoadYahooQuote(
                            $stock['symbol'],
                            $stock['yahoo_symbol']
                        )
                    );

                    $this->em->transactional(
                        function () use ($stock, $crawled) {
                            $this->handle(
                                new UpdateStockPrice(
                                    $stock['id'],
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
                } elseif ($singleStock) {
                    $this->em->transactional(
                        function () use ($stock) {
                            $this->handle(
                                new UpdateHistoricalStockPrice(
                                    $stock['id']
                                )
                            );
                        }
                    );
                } else {
                    // triggering in a separate threat by running shell command line
                    // to avoid memory leak due to exceeds allocated memory.
                    $process = new Process(
                        [
                            'php',
                            'bin/console',
                            'app:update-stock-price',
                            '-e',
                            'dev',
                            '--stock',
                            sprintf(
                                '%s:%s:%s:%s',
                                $stock['id'],
                                $stock['symbol'],
                                $stock['yahoo_symbol'],
                                $stock['market_symbol']
                            ),
                            '--historical',
                        ]
                    );
                    $process->start();

                    $err = null;
                    $process->wait(
                        function ($type, $buffer) {
                            if (Process::ERR === $type) {
                                $err = $buffer;
                            }
                        }
                    );

                    if ($err !== null) {
                        throw new \Exception($err);
                    }
                }
            } catch (\Exception $e) {
                $io->error(
                    sprintf(
                        'failed scraper update stock %s, error: %s',
                        $stock['symbol'],
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
