<?php

namespace App\Command;

use App\Entity\Position;
use App\Repository\StockRepository;
use App\Repository\WalletRepository;
use App\Scrape\YahooStockScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    public function __construct(
        YahooStockScraper $scraper,
        EntityManagerInterface $em,
        StockRepository $stockRepository,
        WalletRepository $walletRepository
    ) {
        parent::__construct();

        $this->em = $em;
        $this->stockRepository = $stockRepository;
        $this->scraper = $scraper;
        $this->walletRepository = $walletRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update all stocks price value based on the yahoo website.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $stocks = $this->stockRepository->findAll();

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

        $this->updateWalletsCapital();

        $this->em->flush();

        $io->progressFinish();

        $io->success('Price updated successfully.');
    }

    private function updateWalletsCapital()
    {
        $wallets = $this->walletRepository->findAll();

        foreach ($wallets as $wallet) {
            $capital = 0;

            /** @var Position $position */
            foreach ($wallet->getPositions(Position::STATUS_OPEN) as $position) {
                $capital += $position->getCapital();
            }

            $wallet->setCapital($capital);

            $this->em->persist($wallet);
        }
    }
}
