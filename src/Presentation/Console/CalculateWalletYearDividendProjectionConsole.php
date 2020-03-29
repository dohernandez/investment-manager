<?php

namespace App\Presentation\Console;

use App\Application\Wallet\Command\CalculateWalletYearDividendProjection;
use App\Infrastructure\Storage\Console\ConsoleWalletRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class CalculateWalletYearDividendProjectionConsole extends Console
{
    protected static $defaultName = 'app:calculate-wallet-year-dividend-projection';

    /**
     * @var ConsoleWalletRepository
     */
    private $walletRepository;

    /**
     * @var ConsoleWalletRepository
     */
    private $consoleWalletRepository;

    public function __construct(
        ConsoleWalletRepository $consoleWalletRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->consoleWalletRepository = $consoleWalletRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Calculate wallet year dividend projection.')
            ->addArgument('wallet', InputArgument::REQUIRED, 'Wallet slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $wallet = $this->consoleWalletRepository->findBySlug($input->getArgument('wallet'));
        if (!$wallet) {
            $io->error(
                sprintf(
                    'wallet not found:%s',
                    $input->getArgument('wallet')
                )
            );
        }

        try {
            $this->bus->dispatch(
                new CalculateWalletYearDividendProjection($wallet['id'], 2020)
            );
        } catch (\Exception $e) {
            $io->error(
                sprintf(
                    'failed caculate wallet year dividend projection:%s, error: %s',
                    $wallet['id'],
                    $e->getMessage()
                )
            );
        }

        $io->success('Wallet year dividend projection calculated successfully.');
    }
}
