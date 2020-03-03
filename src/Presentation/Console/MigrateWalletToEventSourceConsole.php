<?php

namespace App\Presentation\Console;

use App\Application\Wallet\Command\CreateWallet;
use App\Application\Wallet\Repository\AccountRepositoryInterface;
use App\Application\Wallet\Repository\BrokerRepositoryInterface;
use App\Repository\WalletRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class MigrateWalletToEventSourceConsole extends Console
{
    protected static $defaultName = 'app:event-source:migrate-wallet-event-source';

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var BrokerRepositoryInterface
     */
    private $brokerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(
        WalletRepository $walletRepository,
        MessageBusInterface $bus,
        BrokerRepositoryInterface $brokerRepository,
        AccountRepositoryInterface $accountRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($bus);

        $this->walletRepository = $walletRepository;
        $this->brokerRepository = $brokerRepository;
        $this->logger = $logger;
        $this->accountRepository = $accountRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate wallets data to event source');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $wallets = $this->walletRepository->findAll();

        $count = 0;
        $io->progressStart(count($wallets));

        foreach ($wallets as $wallet) {
            $broker = $this->brokerRepository->findByName($wallet->getBroker()->getName());
            if (!$broker) {
                $this->logger->error('Can not migrate wallet due to broker not found', [
                    'wallet_id' => $wallet->getId(),
                    'wallet_name' => $wallet->getName(),
                    'broker_name' => $wallet->getBroker()->getName(),
                ]);
            }

            $account = $this->accountRepository->findByAccountNo($wallet->getBroker()->getAccount()->getAccountNo());
            if (!$account) {
                $this->logger->error('Can not migrate wallet due to account not found', [
                    'wallet_id' => $wallet->getId(),
                    'wallet_name' => $wallet->getName(),
                    'broker_name' => $wallet->getBroker()->getName(),
                    'account_no' => $wallet->getBroker()->getAccount()->getAccountNo(),
                ]);
            }

            $this->bus->dispatch(
                new CreateWallet(
                    $wallet->getName(),
                    $broker,
                    $account
                )
            );

            $count++;
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success($count . ' wallets were migrated successfully.');
    }
}
