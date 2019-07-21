<?php

namespace App\Command;

use App\Entity\Operation;
use App\Repository\OperationRepository;
use App\Repository\WalletRepository;
use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportOperationCommand extends Command
{
    protected static $defaultName = 'app:export-operation';

    /**
     * @var OperationRepository
     */
    private $operationRepository;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(
        OperationRepository $operationRepository,
        WalletRepository $walletRepository
    ) {
        parent::__construct();

        $this->operationRepository = $operationRepository;
        $this->walletRepository = $walletRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Export operations of wallet to csv file')
            ->addArgument('wallet', InputArgument::REQUIRED, 'Wallet slug to export.')
            ->addArgument('filepath', InputArgument::REQUIRED, 'csv file to export')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $slug = $input->getArgument('wallet');
        $wallet = $this->walletRepository->findOneBySlug($slug);

        if (!$wallet) {
            $io->error('wallet ' . $wallet . ' not found');

            return;
        }

        $filepath = $input->getArgument('filepath');
        $writer = Writer::createFromPath($filepath);

        $operations = $this->operationRepository->findBy([
            'wallet' => $wallet,
        ]);

        $count = count($operations);
        $io->progressStart($count);
        foreach ($operations as $operation) {
            $record = [
                '',
                $record[] = $operation->getDateAt()->format('d/m/Y'),
            ];

            $oType = $operation->getType();
            if (in_array($oType, [
                Operation::TYPE_SELL,
                Operation::TYPE_BUY,
                Operation::TYPE_DIVIDEND,
            ])) {
                $record[] = $operation->getStock()->getName();
            } else {
                $record[] = '';
            }

            $record[] = $this->convertType($oType);

            if (in_array($oType, [
                Operation::TYPE_SELL,
                Operation::TYPE_BUY,
            ])) {
                $record[] = $operation->getAmount();
                $record[] = $operation->getPrice() ? $operation->getPrice()->getPreciseValue() : 0;
                $record[] = $operation->getPriceChange() ? $operation->getPriceChange()->getPreciseValue() : 0;
                $record[] = $operation->getPriceChangeCommission() ? $operation->getPriceChangeCommission()->getPreciseValue() : 0;
                $record[] = $operation->getValue() ? $operation->getValue()->getPreciseValue() : 0;
                $record[] = $operation->getCommission() ? $operation->getCommission()->getPreciseValue() : 0;
            } else {
                $record[] = '';
                $record[] = '';
                $record[] = '';
                $record[] = '';
                $record[] = $operation->getValue() ? $operation->getValue()->getPreciseValue() : 0;
                $record[] = '';
            }

            $writer->insertOne($record);

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success($count. ' operations were exported successfully.');
    }



    private function convertType(string $type): string
    {
        switch ($type) {
            case Operation::TYPE_BUY:
                return 'Compra';
            case Operation::TYPE_SELL:
                return 'Venta';
            case Operation::TYPE_CONNECTIVITY:
                return 'Conectividad';
            case Operation::TYPE_INTEREST:
                return 'Interés';
            case Operation::TYPE_DIVIDEND:
                return 'Dividendo';
        }

        throw new \LogicException('type ' . $type . ' not supported');
    }
}
