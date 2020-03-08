<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\SyncStockDividends;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Repository\StockRepository;
use App\Service\StockDividendsService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

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

    public function __construct(
        ProjectionStockRepositoryInterface $stockRepository,
        ProjectionWalletRepositoryInterface $walletRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->stockRepository = $stockRepository;
        $this->walletRepository = $walletRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update all stocks history dividend based on the nasdaq website.')
            ->addOption('symbol', 's', InputOption::VALUE_OPTIONAL, 'Stock symbol')
            ->addOption('wallet', 'w', InputOption::VALUE_OPTIONAL, 'Wallet slug')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

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

        foreach ($stocks as $stock) {
            try {
                $this->bus->dispatch(
                    new SyncStockDividends($stock['id'])
                );
            } catch (\Exception $e) {
                $io->error(sprintf(
                    'failed update stock %s:%s, error: %s',
                    $stock['symbol'],
                    $stock['market_symbol'],
                    $e->getMessage()
                ));
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success('History dividend updated successfully.');
    }
}
