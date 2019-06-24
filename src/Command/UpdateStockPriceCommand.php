<?php

namespace App\Command;

use App\Entity\Position;
use App\Entity\Wallet;
use App\Repository\StockRepository;
use App\Repository\WalletRepository;
use App\Scrape\YahooStockScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('EUR_USD', null, InputOption::VALUE_OPTIONAL, 'rate exchange $ to €')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$eurUSD = $input->getOption('EUR_USD')) {
            $io->error('must define rate exchange EUR_USD');

            return;
        }

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

        $rateExchange[Wallet::RATE_EXCHANGE_EUR_USD] = $eurUSD;

        $this->updateWalletsCapital($rateExchange);

        $this->em->flush();

        $io->progressFinish();

        $io->success('Price updated successfully.');
    }

    private function updateWalletsCapital(array $rateExchange)
    {
        $wallets = $this->walletRepository->findAll();

        foreach ($wallets as $wallet) {
            $capital = 0;
            $wallet->setRateExchange($rateExchange);

            /** @var Position $position */
            foreach ($wallet->getPositions(Position::STATUS_OPEN) as $position) {
                $capital += $position->getCapital();
            }

            $wallet->setCapital($capital);

            $this->em->persist($wallet);
        }
    }
}
