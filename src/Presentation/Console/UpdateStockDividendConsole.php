<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\SyncStockDividends;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProjectionStockRepositoryInterface
     */
    private $stockRepository;

    public function __construct(
        ProjectionStockRepositoryInterface $stockRepository,
        LoggerInterface $logger,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->stockRepository = $stockRepository;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update all stocks history dividend based on the nasdaq website.')
            ->addOption('symbol', 's', InputOption::VALUE_OPTIONAL, 'Stock symbol')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $stocks = [];
        if ($symbol = $input->getOption('symbol')) {
            if ($stock = $this->stockRepository->findBySymbol($symbol)) {
                $stocks[] = $stock;
            }
        } else {
            $stocks = $this->stockRepository->findAll();
        }

        $io->progressStart(count($stocks));

        foreach ($stocks as $stock) {
            try {
                $this->bus->dispatch(
                    new SyncStockDividends($stock->getId())
                );
            } catch (\Exception $e) {
                $io->error(sprintf(
                    'failed update stock %s, error: %s',
                    $stock->getSymbol(),
                    $e->getMessage()
                ));
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success('History dividend updated successfully.');
    }
}
