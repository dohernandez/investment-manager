<?php

namespace App\Presentation\Console;

use App\Application\Transfer\Command\RegisterTransfer;
use App\Application\Transfer\Repository\AccountRepositoryInterface;
use App\Infrastructure\Money\Money;
use App\Repository\TransferRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class MigrateTransferToEventSourceConsole extends Console
{
    protected static $defaultName = 'app:event-source:migrate-transfer-event-source';

    /**
     * @var TransferRepository
     */
    private $transferRepository;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(
        TransferRepository $transferRepository,
        AccountRepositoryInterface $accountRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->transferRepository = $transferRepository;
        $this->accountRepository = $accountRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate transfers data to event source');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $transfers = $this->transferRepository->findAll();

        $count = 0;
        $io->progressStart(count($transfers));

        foreach ($transfers as $transfer) {
            $beneficiary = $this->accountRepository->findByAccountNo($transfer->getBeneficiaryParty()->getAccountNo());
            $debtor = $this->accountRepository->findByAccountNo($transfer->getDebtorParty()->getAccountNo());
            $this->bus->dispatch(
                new RegisterTransfer(
                    $beneficiary,
                    $debtor,
                    $this->convertMoneyEventSource($transfer->getAmount()),
                    $transfer->getDate()
                )
            );

            $count++;
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success($count . ' transfers were migrated successfully.');
    }
}
