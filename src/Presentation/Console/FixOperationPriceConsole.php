<?php

namespace App\Presentation\Console;

use App\Application\Wallet\Repository\OperationRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Domain\Wallet\Operation;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Storage\Console\ConsoleOperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class FixOperationPriceConsole extends Console
{
    protected static $defaultName = 'app:fix-operation-price';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    /**
     * @var OperationRepositoryInterface
     */
    private $operationRepository;

    /**
     * @var ConsoleOperationRepository
     */
    private $consoleOperationRepository;

    public function __construct(
        EntityManagerInterface $em,
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        ConsoleOperationRepository $consoleOperationRepository,
        OperationRepositoryInterface $operationRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->em = $em;
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->operationRepository = $operationRepository;
        $this->consoleOperationRepository = $consoleOperationRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fix operations price of wallet from csv file')
            ->addArgument('wallet', InputArgument::REQUIRED, 'Wallet slug to import.')
            ->addArgument('filepath', InputArgument::REQUIRED, 'csv file to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $slug = $input->getArgument('wallet');
        $wallet = $this->projectionWalletRepository->findBySlug($slug);

        if (!$wallet) {
            $io->error('wallet ' . $slug . ' not found');

            return;
        }

        $filepath = $input->getArgument('filepath');
        $reader = Reader::createFromPath($filepath, 'r');
        $records = [];
        foreach ($reader->getRecords() as $record) {
            if ($record[3] === 'Compra' || $record[3] === 'Venta') {
                $records[] = $record;
            }
        }

        $count = \count($records);

        $io->progressStart($count);

        $walletCurrency = $wallet->getCurrency();
        foreach ($records as $offset => $record) {
            $operation = $this->consoleOperationRepository->findByDateAtTypeAmountValueAndCommissions(
                \DateTime::createFromFormat('d/m/Y', $record[1]),
                $this->parserType($record[3]),
                intval($record[4]),
                new Money($walletCurrency, $this->parserPrice($record[8])),
                new Money($walletCurrency, $this->parserPrice($record[9]))
            );

            $this->em->transactional(
                function () use ($operation, $record) {
                    $operation = $this->operationRepository->find($operation['id']);
                    $operation->fixPrice(
                        new Money($operation->getStock()->getCurrency(), $this->parserPrice($record[5]))
                    );

                    $this->operationRepository->save($operation);
                }
            );

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success($count . ' operations were imported successfully.');
    }

    private function parserType(string $type): string
    {
        switch ($type) {
            case 'Compra':
                return Operation::TYPE_BUY;
            case 'Venta':
                return Operation::TYPE_SELL;
            case 'Conectividad':
                return Operation::TYPE_CONNECTIVITY;
            case 'Interés':
                return Operation::TYPE_INTEREST;
            case 'Dividendo':
                return Operation::TYPE_DIVIDEND;
            case 'Split/Reverse':
                return Operation::TYPE_SPLIT_REVERSE;
        }

        throw new \LogicException('type ' . $type . ' not supported');
    }

    private function parserPrice(string $price, int $divisor = 100): int
    {
        $price = str_replace(',', '.', $price);

        return floatval($price) * $divisor;
    }
}
