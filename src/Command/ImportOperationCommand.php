<?php

namespace App\Command;

use App\Entity\Operation;
use App\Message\UpdateWalletCapital;
use App\Repository\StockRepository;
use App\Repository\WalletRepository;
use App\VO\Money;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use function PHPSTORM_META\type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

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

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        EntityManagerInterface $em,
        StockRepository $stockRepository,
        WalletRepository $walletRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct();

        $this->em = $em;
        $this->stockRepository = $stockRepository;
        $this->walletRepository = $walletRepository;
        $this->bus = $bus;
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

        $walletCurrency = $wallet->getCurrency();
        foreach ($records as $offset => $record) {
            $operation = new Operation();

            $operation
                ->setDateAt(\DateTime::createFromFormat('d/m/Y', $record[1]))
                ->setType($this->parserType($record[3]))
                ->setAmount(intval($record[4]))
                ->setPriceChangeCommission(Money::from($walletCurrency, $this->parserPrice($record[7])))
                ->setValue(Money::from($walletCurrency, $this->parserPrice($record[8])))
                ->setCommission(Money::from($walletCurrency, $this->parserPrice($record[9])))
            ;

            $operationType = $operation->getType();
            if (in_array($operationType, [
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

                if ($operationType != Operation::TYPE_DIVIDEND) {
                    $stockCurrency = $stock->getCurrency();

                    $price = $this->parserPrice($record[5]);
                    if (!empty($price)) {
                        $operation->setPrice(Money::from($stockCurrency, $price));
                    }

                    $priceChange = $this->parserPrice($record[6], 10000);
                    if (!empty($priceChange)) {
                        $operation->setPriceChange(Money::from($stockCurrency, $priceChange), 4);
                    }
                }

                $operation->setStock($stock);
            }

            $operation->setWallet($wallet);

            $this->em->persist($operation);
            $io->progressAdvance();
        }

        $this->em->flush();

        $this->bus->dispatch(new UpdateWalletCapital($wallet));

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

    private function parserPrice(string $price, int $divisor = 100): int
    {
        $price = str_replace(',', '.', $price);

        return floatval($price) * $divisor;
    }
}
