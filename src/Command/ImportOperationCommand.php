<?php

namespace App\Command;

use App\Entity\Operation;
use App\Repository\StockRepository;
use App\Repository\WalletRepository;
use App\VO\Money;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportOperationCommand extends Command
{
    protected static $defaultName = 'app:import-operation';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var StockRepository
     */
    private $stockRepository;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(
        EntityManagerInterface $em,
        StockRepository $stockRepository,
        WalletRepository $walletRepository
    ) {
        parent::__construct();

        $this->em = $em;
        $this->stockRepository = $stockRepository;
        $this->walletRepository = $walletRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import operations of wallet from csv file')
            ->addArgument('wallet', InputArgument::REQUIRED, 'Wallet slug to import.')
            ->addArgument('filepath', InputArgument::REQUIRED, 'csv file to import')
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
        $reader = Reader::createFromPath($filepath, 'r');
        $records = $reader->getRecords();

        $count = iterator_count($records);

        $io->progressStart($count);

        $wCurrency = $wallet->getCurrency();
        foreach ($records as $offset => $record) {
            $operation = new Operation();

            $operation
                ->setDateAt(\DateTime::createFromFormat('d/m/Y', $record[1]))
                ->setType($this->parserType($record[3]))
                ->setAmount(intval($record[4]))
                ->setPriceChangeCommission(Money::from($wCurrency, floatval($record[7])))
                ->setValue(Money::from($wCurrency, floatval($record[8])))
                ->setCommission(Money::from($wCurrency, floatval($record[9])))
            ;

            $oType = $operation->getType();
            if (in_array($oType, [
                Operation::TYPE_BUY,
                Operation::TYPE_SELL,
                Operation::TYPE_DIVIDEND,
            ])) {
                $stock = $this->stockRepository->findOneBy(['name' => $record[2]]);
                if ($stock === null) {
                    $io->error('failed to add operation ' . json_encode($record) . ', stock not found');

                    $io->progressAdvance();
                    continue;
                }

                $sCurrency = $stock->getCurrency();

                if (!empty(floatval($record[5]))) {
                    $operation->setPrice(Money::from($sCurrency, floatval($record[5])));
                }

                if (!empty(floatval($record[6]))) {
                    $operation->setPriceChange(Money::from($sCurrency, floatval($record[6])));
                }

                $operation->setStock($stock);
            }

            $operation->setWallet($wallet);

            $this->em->persist($operation);
            $io->progressAdvance();
        }

        $this->em->flush();
        $io->progressFinish();


        $io->success($count. ' operations were imported successfully.');
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
        }

        throw new \LogicException('type ' . $type . ' not supported');
    }
}
