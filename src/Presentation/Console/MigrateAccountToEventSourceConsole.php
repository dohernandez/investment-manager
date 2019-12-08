<?php

namespace App\Presentation\Console;

use App\Application\Account\Command\OpenAccountCommand;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Infrastructure\Money\Currency;
use App\Repository\AccountRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class MigrateAccountToEventSourceConsole extends Command
{
    protected static $defaultName = 'app:event-source:migrate-account-event-source';

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        AccountRepository $accountRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->bus = $bus;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate accounts data to event source');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $accounts = $this->accountRepository->findAll();

        $count = 0;
        $io->progressStart(count($accounts));

        foreach ($accounts as $account) {
            $this->bus->dispatch(
                new OpenAccountCommand(
                    $account->getName(),
                    $account->getType(),
                    $account->getAccountNo(),
                    Currency::eur()
                )
            );

            $count ++;
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success($count. ' accounts were migrated successfully.');
    }
}
