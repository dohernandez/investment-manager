<?php

namespace App\Command;

use App\Client\CurrencyConverter;
use App\Entity\Exchange;
use App\Message\ExchangeRateUpdated;
use App\Repository\BrokerRepository;
use App\Repository\ExchangeRepository;
use App\Repository\StockMarketRepository;
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

    public function __construct(
        EntityManagerInterface $em,
        BrokerRepository $brokerRepository,
        StockMarketRepository $stockMarketRepository,
        ExchangeRepository $exchangeRepository,
        CurrencyConverter $currencyConverter,
        MessageBusInterface $bus
    ) {
        parent::__construct();

        $this->brokerRepository = $brokerRepository;
        $this->stockMarketRepository = $stockMarketRepository;
        $this->em = $em;
        $this->currencyConverter = $currencyConverter;
        $this->exchangeRepository = $exchangeRepository;
        $this->bus = $bus;
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

        $exchangeRates = [];
        foreach ($brokers as $broker) {
            foreach ($stockMarkets as $stockMarket) {
                $k = $broker->getCurrency()->getCurrencyCode() . '_' . $stockMarket->getCurrency()->getCurrencyCode();

                if (isset($exchangeRates[$k])) {
                    continue;
                }

                $exchangeRate = $this->exchangeRepository->findOneBy([
                    'paarCurrency' => $k,
                ]);

                if ($exchangeRate === null) {
                    $exchangeRate = (new Exchange())
                        ->setFromCurrency($broker->getCurrency())
                        ->setToCurrency($stockMarket->getCurrency())
                        ->setPaarCurrency($k)
                    ;
                }

                $exchangeRates[$k] = $exchangeRate;
            }
        }

//        $this->currencyConverter->updateRates($exchangeRates);

        foreach ($exchangeRates as $exchangeRate) {
            $this->em->persist($exchangeRate);
        }

        $this->em->flush();

        $this->bus->dispatch(new ExchangeRateUpdated($exchangeRates));

        $io->success('exchange rate updated successfully.');
    }
}
