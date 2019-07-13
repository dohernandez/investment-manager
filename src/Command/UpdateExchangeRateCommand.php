<?php

namespace App\Command;

use App\Client\CurrencyConverter;
use App\Entity\Exchange;
use App\Message\UpdateWalletCapital;
use App\Repository\BrokerRepository;
use App\Repository\ExchangeRepository;
use App\Repository\StockMarketRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateExchangeRateCommand extends Command
{
    protected static $defaultName = 'app:update-exchange-rate';

    /**
     * @var BrokerRepository
     */
    private $brokerRepository;

    /**
     * @var StockMarketRepository
     */
    private $stockMarketRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CurrencyConverter
     */
    private $currencyConverter;

    /**
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(
        EntityManagerInterface $em,
        BrokerRepository $brokerRepository,
        StockMarketRepository $stockMarketRepository,
        ExchangeRepository $exchangeRepository,
        CurrencyConverter $currencyConverter,
        WalletRepository $walletRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct();

        $this->brokerRepository = $brokerRepository;
        $this->stockMarketRepository = $stockMarketRepository;
        $this->em = $em;
        $this->currencyConverter = $currencyConverter;
        $this->exchangeRepository = $exchangeRepository;
        $this->bus = $bus;
        $this->walletRepository = $walletRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create/Update exchange rate based on the brokers and markets defined.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $brokers = $this->brokerRepository->findAll();
        $stockMarkets = $this->stockMarketRepository->findAll();

        foreach ($brokers as $broker) {
            $exchanges = [];

            foreach ($stockMarkets as $stockMarket) {
                $k = $broker->getCurrency()->getCurrencyCode() . '_' . $stockMarket->getCurrency()->getCurrencyCode();

                if (isset($exchanges[$k])) {
                    continue;
                }

                $exchange = $this->exchangeRepository->findOneBy([
                    'paarCurrency' => $k,
                ]);


                if ($exchange === null) {
                    $exchange = (new Exchange())
                        ->setFromCurrency($broker->getCurrency())
                        ->setToCurrency($stockMarket->getCurrency())
                        ->setPaarCurrency($k)
                    ;
                }

                $exchanges[$k] = $exchange;
            }

            $this->currencyConverter->updateRates($exchanges);

            foreach ($exchanges as $exchange) {
                $this->em->persist($exchange);
            }

        }

        $this->em->flush();$wallets = $this->walletRepository->findAll();
        foreach ($wallets as $wallet) {
            $this->bus->dispatch(new UpdateWalletCapital($wallet));
        }

        $io->success('exchange rate updated successfully.');
    }
}
