<?php

namespace App\Command;

use App\Message\UpdateWalletCapital;
use App\Repository\StockRepository;
use App\Repository\WalletRepository;
use App\Scrape\YahooStockScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateStockPriceCommand extends Command
{
    protected static $defaultName = 'app:update-stock-price';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var StockRepository
     */
    private $stockRepository;

    /**
     * @var YahooStockScraper
     */
    private $scraper;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        YahooStockScraper $scraper,
        EntityManagerInterface $em,
        StockRepository $stockRepository,
        WalletRepository $walletRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct();

        $this->em = $em;
        $this->stockRepository = $stockRepository;
        $this->scraper = $scraper;
        $this->walletRepository = $walletRepository;
        $this->bus = $bus;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update all stocks price value based on the yahoo website.')
            ->addOption('symbol', 's', InputOption::VALUE_OPTIONAL, 'Stock symbol')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($symbol = $input->getOption('symbol')) {
            $stocks = $this->stockRepository->findBy([
                'symbol' => $symbol,
            ]);
        } else {
            $stocks = $this->stockRepository->findAll();
        }

        $io->progressStart(count($stocks));

        foreach ($stocks as $stock) {
            // Update stock price, description and sector or industry if missing
            // values from yahoo sources.
            try {
                $this->scraper->updateFromQuote($stock);

                $this->em->persist($stock);
            } catch (\Exception $e) {
                $io->error(sprintf(
                    'failed scraper update stock %s, error: %s',
                    $stock->getSymbol(),
                    $e->getMessage()
                ));
            }

            $io->progressAdvance();
        }

        $this->em->flush();

        $wallets = $this->walletRepository->findAll();
        foreach ($wallets as $wallet) {
            $this->bus->dispatch(new UpdateWalletCapital($wallet));
        }

        $io->progressFinish();

        $io->success('Price updated successfully.');
    }
}
