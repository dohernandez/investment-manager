<?php

namespace App\Presentation\Console;

use App\Application\Wallet\Command\ReCalculateWalletCapital;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Infrastructure\Storage\Console\ConsoleWalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class ReCalculateWalletCapitalConsole extends Console
{
    protected static $defaultName = 'app:re-calculate-wallet-capital';

    /**
     * @var ConsoleWalletRepository
     */
    private $consoleWalletRepository;

    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        ConsoleWalletRepository $consoleWalletRepository,
        WalletRepositoryInterface $walletRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->consoleWalletRepository = $consoleWalletRepository;
        $this->walletRepository = $walletRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Re calculate wallet capital.')
            ->addOption('slug', 's', InputOption::VALUE_OPTIONAL, 'Wallet symbol')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $wallets = [];
        if ($slug = $input->getOption('slug')) {
            if ($wallet = $this->consoleWalletRepository->findBySlug($slug)) {
                $wallets[] = $wallet;
            }
        } else {
            $wallets = $this->consoleWalletRepository->findAll();
        }

        $io->progressStart(count($wallets));

        $this->em->transactional(function () use ($wallets, $io) {
            foreach ($wallets as $wallet) {
                $this->bus->dispatch(
                    new ReCalculateWalletCapital(
                        $wallet['id']
                    )
                );

                $io->progressAdvance();
            }
        });

        $io->progressFinish();

        $io->success('Re calculate wallet capital successfully.');
    }
}
