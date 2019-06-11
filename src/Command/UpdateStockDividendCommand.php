<?php

namespace App\Command;

use App\Repository\StockRepository;
use App\Scrape\NasdaqDividendScraper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateStockDividendCommand extends Command
{
    protected static $defaultName = 'app:update-stock-dividend';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var StockRepository
     */
    private $stockRepository;

    /**
     */
    private $scraper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        NasdaqDividendScraper $scraper,
        EntityManagerInterface $em,
        StockRepository $stockRepository,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->em = $em;
        $this->stockRepository = $stockRepository;
        $this->scraper = $scraper;
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
                $this->scraper->updateHistoricalDividend($stock);

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

        $io->progressFinish();

        $io->success('History dividend updated successfully.');
    }
}
