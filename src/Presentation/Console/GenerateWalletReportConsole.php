<?php

namespace App\Presentation\Console;

use App\Application\Report\Command\GenerateDailyWalletReport;
use App\Infrastructure\Storage\Console\ConsoleWalletRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

use function in_array;
use function sprintf;

class GenerateWalletReportConsole extends Console
{
    protected static $defaultName = 'app:generate-report-wallet';

    /**
     * @var ConsoleWalletRepository
     */
    private $consoleWalletRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        ConsoleWalletRepository $consoleWalletRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->consoleWalletRepository = $consoleWalletRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Generate wallet report. Overwrite existing report if it is run for an existing date.'
            )
            ->addOption('slug', 's', InputOption::VALUE_OPTIONAL, 'Wallet symbol')
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Report type. Valid values [daily, weekly, annually]'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $type = $input->getOption('type');
        if (!in_array($type, ['daily', 'weekly', 'annually'])) {
            $io->error(sprintf('Type %q is not supported', $type));

            return;
        }

        $wallets = [];
        if ($slug = $input->getOption('slug')) {
            if ($wallet = $this->consoleWalletRepository->findBySlug($slug)) {
                $wallets[] = $wallet;
            }
        } else {
            $wallets = $this->consoleWalletRepository->findAll();
        }

        $io->progressStart(count($wallets));

        $this->em->transactional(
            function () use ($type, $wallets, $io) {
                foreach ($wallets as $wallet) {
                    switch ($type) {
                        case 'daily':
                            $this->bus->dispatch(new GenerateDailyWalletReport($wallet['id'], new DateTime()));
                            break;
                    }

                    $io->progressAdvance();
                }
            }
        );

        $io->progressFinish();

        $io->success('Re calculate wallet capital successfully.');
    }
}
