<?php

namespace App\Command;

use App\Entity\Transfer;
use App\Repository\AccountRepository;
use App\VO\Money;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportTransferCommand extends Command
{
    protected static $defaultName = 'app:import-transfer';

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
            ->setDescription('Import transfers from csv file')
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
            $beneficiary = $this->accountRepository->findOneBy([
                'alias' => $record[2]
            ]);
            $debtor = $this->accountRepository->findOneBy([
                'alias' => $record[1]
            ]);


            $transfer = new Transfer();
            $transfer
                ->setDate(\DateTime::createFromFormat('d/m/Y', $record[0]))
                ->setBeneficiaryParty($beneficiary)
                ->setDebtorParty($debtor)
                ->setAmount(Money::fromEURValue(floatval($record[3])))
            ;

            $this->em->persist($transfer);

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success($count. ' stock markets were imported successfully.');
    }
}
