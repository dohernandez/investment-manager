<?php

namespace App\Command;

use App\Entity\Account;
use App\Entity\StockMarket;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportAccountCommand extends Command
{
    protected static $defaultName = 'app:import-account';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import accounts from csv file')
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
            $account = new Account();
            $account->setName($record[0])
                ->setAccountNo($record[1])
                ->setAlias($record[2])
                ->setType($record[3])
            ;

            $this->em->persist($account);

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success($count. ' stock markets were imported successfully.');
    }
}
