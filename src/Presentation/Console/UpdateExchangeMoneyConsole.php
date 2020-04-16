<?php

namespace App\Presentation\Console;

use App\Application\ExchangeMoney\Command\UpdateMoneyRates;
use App\Application\ExchangeMoney\Command\UpdateMoneyRatesHistorical;
use App\Application\ExchangeMoney\Repository\MarketRepositoryInterface;
use App\Application\ExchangeMoney\Repository\WalletRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

use function in_array;

final class UpdateExchangeMoneyConsole extends Console
{
    protected static $defaultName = 'app:update-exchange-rate';

    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var MarketRepositoryInterface
     */
    private $marketRepository;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        MarketRepositoryInterface $marketRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->walletRepository = $walletRepository;
        $this->marketRepository = $marketRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create/Update exchange money rates based on the wallet and markets defined.')
            ->addOption(
                'historical',
                null,
                InputOption::VALUE_OPTIONAL,
                'Update historical data instead',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->progressStart(3);

        $wallets = $this->walletRepository->findAll();
        $markets = $this->marketRepository->findAll();

        $io->progressAdvance();

        $paarCurrencies = [];
        foreach ($wallets as $wallet) {
            foreach ($markets as $market) {
                $paar = $wallet->getCurrency()->getCurrencyCode() . '_' . $market->getCurrency()->getCurrencyCode();

                if (in_array($paar, $paarCurrencies)) {
                    continue;
                }

                $paarCurrencies[] = $paar;
            }
        }

        $io->progressAdvance();

        if (!$this->isHistorical($input)) {
            $this->bus->dispatch(
                new UpdateMoneyRates(
                    $paarCurrencies
                )
            );
        } else {
            $this->bus->dispatch(
                new UpdateMoneyRatesHistorical(
                    $paarCurrencies
                )
            );
        }

        $io->progressFinish();

        $io->success('exchange money rates updated successfully.');
    }
}
