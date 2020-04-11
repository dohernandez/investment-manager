<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\LoadYahooQuote;
use App\Application\Market\Command\UpdateHistoricalStockPrice;
use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Scraper\StockCrawled;
use App\Infrastructure\Context;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Storage\Console\ConsoleStockRepository;
use App\Infrastructure\Storage\Console\ConsoleWalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateStockPriceHistoricalConsole extends Console
{
    protected static $defaultName = 'app:update-stock-price-historical';

    /**
     * @var ConsoleWalletRepository
     */
    private $consoleWalletRepository;

    /**
     * @var ConsoleStockRepository
     */
    private $consoleStockRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        ConsoleWalletRepository $consoleWalletRepository,
        ConsoleStockRepository $consoleStockRepository,
        MessageBusInterface $bus,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        parent::__construct($bus);

        $this->consoleWalletRepository = $consoleWalletRepository;
        $this->consoleStockRepository = $consoleStockRepository;
        $this->logger = $logger;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update all stocks price value history based on the yahoo website.')
            ->addOption('symbol', 's', InputOption::VALUE_OPTIONAL, 'Stock symbol')
            ->addOption('wallet', 'w', InputOption::VALUE_OPTIONAL, 'Wallet slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $stocks = [];
        if ($symbol = $input->getOption('symbol')) {
            if ($stock = $this->consoleStockRepository->findBySymbol($symbol)) {
                $stocks[] = $stock;
            }
        } elseif ($wallet = $input->getOption('wallet')) {
            $stocks = $this->consoleWalletRepository->findAllStocksInWalletOnOpenPositionBySlug($wallet);
        } else {
            $stocks = $this->consoleStockRepository->findAllListed();
        }

        $context = Context\Logger::toContext(Context\Context::TODO(), $this->logger);

        $io->progressStart(count($stocks));

        foreach ($stocks as $stock) {
            $context = $context->addKeysAndValues(
                [
                    'symbol' => $stock['symbol'],
                ]
            );
            try {
                Logger::debug($context, 'Crawling historical stock');

                $this->em->transactional(
                    function () use ($stock) {
                        $this->handle(
                            new UpdateHistoricalStockPrice(
                                $stock['id']
                            )
                        );
                    }
                );
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
