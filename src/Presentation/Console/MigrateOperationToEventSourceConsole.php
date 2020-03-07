<?php

namespace App\Presentation\Console;

use App\Application\Market\Repository\StockDividendRepositoryInterface;
use App\Application\Wallet\Command\RegisterOperation;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\StockRepositoryInterface;
use App\Domain\Market;
use App\Domain\Wallet\Operation;
use App\Domain\Wallet\Stock;
use App\Repository\OperationRepository;
use DateTime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

use function in_array;

final class MigrateOperationToEventSourceConsole extends Console
{
    protected static $defaultName = 'app:event-source:migrate-operation-event-source';

    /**
     * @var OperationRepository
     */
    private $operationRepository;

    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StockDividendRepositoryInterface
     */
    private $stockDividendRepository;

    public function __construct(
        OperationRepository $operationRepository,
        ProjectionWalletRepositoryInterface $walletRepository,
        StockRepositoryInterface $stockRepository,
        StockDividendRepositoryInterface $stockDividendRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->operationRepository = $operationRepository;
        $this->walletRepository = $walletRepository;
        $this->stockRepository = $stockRepository;
        $this->stockDividendRepository = $stockDividendRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate operations data to event source')
            ->addArgument('wallet', InputArgument::REQUIRED, 'Wallet slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $wSlug = $input->getArgument('wallet');
        $wallet = $this->walletRepository->findBySlug($wSlug);
        if (!$wallet) {
            $io->error(
                sprintf(
                    'wallet not found [%]',
                    $wSlug
                )
            );

            return;
        }

        $operations = $this->operationRepository->findAll();

        $count = 0;
        $io->progressStart(count($operations));

        foreach ($operations as $operation) {
            // this is for those sells and buys saves, but in fact, they are split/reverse
            if (in_array($operation->getType(), [Operation::TYPE_BUY, Operation::TYPE_SELL])) {
                $operation->setPrice(\App\VO\Money::fromCurrency($operation->getStock()->getCurrency()));
            }

            $stock = null;
            if (in_array($operation->getType(), Operation::TYPES_STOCK)) {
                $stock = $this->stockRepository->findBySymbol($operation->getStock()->getSymbol());
                if (!$stock) {
                    $io->error(
                        sprintf(
                            'stock not found [%]',
                            $operation->getStock()->getSymbol()
                        )
                    );

                    continue;
                }
            }

            $dateAt = (new DateTime(null, $operation->getDateAt()->getTimezone()))->setTimestamp(
                $operation->getDateAt()->getTimestamp()
            );

            if ($operation->getType() === Operation::TYPE_DIVIDEND) {
                $dividend = $this->stockDividendRepository->findLastBeforeDateByStock(
                    new Market\Stock($stock->getId()),
                    $dateAt
                );

                $stock = $stock->changePrevDividendExDate($dividend ? $dividend->getExDate() : $dateAt);
            }

            try {
                $this->bus->dispatch(
                    new RegisterOperation(
                        $wallet->getId(),
                        $dateAt,
                        $operation->getType(),
                        $this->convertMoneyEventSource($operation->getValue()),
                        $stock,
                        $operation->getAmount(),
                        $this->convertMoneyEventSource($operation->getPrice()),
                        $this->convertMoneyEventSource($operation->getPriceChange()),
                        $this->convertMoneyEventSource($operation->getPriceChangeCommission()),
                        $this->convertMoneyEventSource($operation->getCommission())
                    )
                );
            } catch (\Exception $e) {
                $io->error(
                    sprintf(
                        'failed register operation [%s]. Error: %s',
                        (string)$operation,
                        $e
                    )
                );

                return;
            } finally {
                $count++;
                $io->progressAdvance();
            }
        }

        $io->progressFinish();
        $io->success($count . ' operations were migrated successfully.');
    }
}
