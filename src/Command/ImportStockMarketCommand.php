<?php

namespace App\Command;

use App\Entity\StockMarket;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportStockMarketCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected static $defaultName = 'app:import-stock-market';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('filepath', InputArgument::REQUIRED, 'csv file to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $filepath = $input->getArgument('filepath');

        $reader = Reader::createFromPath($filepath, 'r');
        $records = $reader->getRecords();

        $count = iterator_count($records);

        $io->progressStart($count);

        foreach ($records as $offset => $record) {
            $stockMarket = new StockMarket();
            $stockMarket->setName($record[0])
                ->setSymbol($record[1])
                ->setCountry($record[2])
                ->setYahooSymbol($record[3])
                ;

            $this->em->persist($stockMarket);

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success($count. ' stock markets were imported successfully.');
    }
}
