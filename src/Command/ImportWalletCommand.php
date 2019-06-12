<?php

namespace App\Command;

use App\Entity\Broker;
use App\Entity\Wallet;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportWalletCommand extends Command
{
    protected static $defaultName = 'app:import-wallet';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    public function __construct(EntityManagerInterface $em, AccountRepository $accountRepository)
    {
        parent::__construct();

        $this->em = $em;
        $this->accountRepository = $accountRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import wallets from csv file')
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
            $account = $this->accountRepository->findOneBy([
                'alias' => $record[1]
            ]);

            $broker = new Broker();
            $broker
                ->setName($record[1])
                ->setSite($record[0])
                ->setAccount($account)
            ;

            $wallet = new Wallet();
            $wallet->setName($record[0]);

            $broker->setWallet($wallet);
            $wallet->setBroker($broker);

            $this->em->persist($broker);
            $this->em->persist($wallet);

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success($count. ' stock markets were imported successfully.');
    }
}
