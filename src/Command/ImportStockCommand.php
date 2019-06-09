<?php

namespace App\Command;

use App\Entity\Stock;
use App\Entity\StockInfo;
use App\Repository\StockInfoRepository;
use App\Repository\StockMarketRepository;
use App\Scrape\YahooStockScraper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportStockCommand extends Command
{
    protected static $defaultName = 'app:import-stock';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var StockInfoRepository
     */
    private $stockInfoRepository;

    /**
     * @var StockMarketRepository
     */
    private $stockMarketRepository;

    /**
     * @var YahooStockScraper
     */
    private $scraper;

    public function __construct(
        EntityManagerInterface $em,
        StockInfoRepository $stockInfoRepository,
        StockMarketRepository $stockMarketRepository,
        YahooStockScraper $scraper
    ) {
        parent::__construct();

        $this->em = $em;
        $this->stockInfoRepository = $stockInfoRepository;
        $this->stockMarketRepository = $stockMarketRepository;
        $this->scraper = $scraper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import stocks from csv file')
            ->addArgument('filepath', InputArgument::REQUIRED, 'csv file to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $filepath = $input->getArgument('filepath');

        $reader = Reader::createFromPath($filepath, 'r');
        $records = $reader->getRecords();

        $stockInfos = new ArrayCollection();
        $count = iterator_count($records);

        $io->progressStart($count);

        foreach ($records as $offset => $record) {

            $stockMarket = $this->stockMarketRepository->findOneBy([
                'symbol' => $record[1]
            ]);

            $stockInfoType = $stockInfos->get(StockInfo::TYPE.'.'.$record[3]);
            if ($stockInfoType === null) {
                $stockInfoType = $this->findOrCreateStockInfo(StockInfo::TYPE, $record[3]);
                $stockInfos->set(StockInfo::TYPE.'.'.$record[3], $stockInfoType);
            }

            $stockInfoSector = $stockInfos->get(StockInfo::SECTOR.'.'.$record[4]);
            if ($stockInfoSector === null) {
                $stockInfoSector = $this->findOrCreateStockInfo(StockInfo::SECTOR, $record[4]);
                $stockInfos->set(StockInfo::SECTOR.'.'.$record[4], $stockInfoSector);
            }

            $stockInfoIndustry = $stockInfos->get(StockInfo::INDUSTRY.'.'.$record[5]);
            if ($stockInfoIndustry === null) {
                $stockInfoIndustry = $this->findOrCreateStockInfo(StockInfo::INDUSTRY, $record[5]);
                $stockInfos->set(StockInfo::INDUSTRY.'.'.$record[5], $stockInfoIndustry);
            }

            $stock = new Stock();
            $stock->setName($record[0])
                ->setMarket($stockMarket)
                ->setSymbol($record[2])
                ->setType($stockInfoType)
                ->setSector($stockInfoSector)
                ->setIndustry($stockInfoIndustry)
                ;

            // Update stock price, description and sector or industry if missing
            // values from yahoo sources.
            try {
                $this->scraper->update($stock, $stockInfos);
            } catch (\Exception $e) {
                $io->error(sprintf(
                    'failed scraper update stock %s, error: %s',
                    $stock->getSymbol(),
                    $e->getMessage()
                ));
            }

            $this->em->persist($stock);

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success($count. ' stocks were imported successfully.');
    }

    protected function findOrCreateStockInfo(string $type, string $name): StockInfo
    {
        $stockInfo = $this->stockInfoRepository->findOneBy([
            'type' => $type,
            'name' => $name
        ]);

        if ($stockInfo === null) {
            $stockInfo = new StockInfo();
            $stockInfo->setType($type)
                ->setName($name)
            ;
        }

        return $stockInfo;
    }
}
