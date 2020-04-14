<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\SyncStockDividends;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Infrastructure\Process\WaitGroup;
use App\Repository\StockRepository;
use App\Service\StockDividendsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UpdateStockDividendConsole extends Console
{
    protected static $defaultName = 'app:update-stock-dividend';

    /**
     * @var ProjectionStockRepositoryInterface
     */
    private $stockRepository;

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
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->stockRepository = $stockRepository;
        $this->walletRepository = $walletRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Update all stocks history dividend based on the nasdaq website.')
            ->addOption('symbol', 's', InputOption::VALUE_OPTIONAL, 'Stock symbol')
            ->addOption('wallet', 'w', InputOption::VALUE_OPTIONAL, 'Wallet slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (parent::execute($input, $output)) {
            return;
        }

        $threads = (int)$input->getOption('threads');

        $stocks = [];
        if ($symbol = $input->getOption('symbol')) {
            if ($stock = $this->stockRepository->findBySymbol($symbol)) {
                $stocks[] = [
                    'id'            => $stock->getId(),
                    'symbol'        => $stock->getSymbol(),
                    'market_symbol' => $stock->getMarket()->getSymbol(),
                ];
            }
        } elseif ($wallet = $input->getOption('wallet')) {
            $wStocks = $this->walletRepository->findAllStocksInWalletOnOpenPositionBySlug($wallet);
            foreach ($wStocks as $stock) {
                $stocks[] = [
                    'id'            => $stock->getId(),
                    'symbol'        => $stock->getSymbol(),
                    'market_symbol' => $stock->getMarket()->getSymbol(),
                ];
            }
        } else {
            $stocksListed = $this->stockRepository->findAllListed();
            foreach ($stocksListed as $stock) {
                $stocks[] = [
                    'id'            => $stock->getId(),
                    'symbol'        => $stock->getSymbol(),
                    'market_symbol' => $stock->getMarket()->getSymbol(),
                ];
            }
        }

        $io->progressStart(count($stocks));
        $wg = new WaitGroup();
        $r = 0;

        foreach ($stocks as $stock) {
            $this->process(
                self::$defaultName,
                'processSyncStockDividends',
                [
                    $stock['id'],
                ],
                $wg
            );

            $r++;
            if ($r === $threads) {
                $r = $wg->wait(1);
            }

            $io->progressAdvance();
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

        $io->success('History dividend updated successfully.');
    }

    /**
     * @param array $stock [0:id => ...]
     */
    protected function processSyncStockDividends(array $stock): void
    {
        $this->em->transactional(
            function () use ($stock) {
                $this->bus->dispatch(
                    new SyncStockDividends($stock[0])
                );
            }
        );
    }
}
