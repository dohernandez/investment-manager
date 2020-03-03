<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\RegisterStockMarket;
use App\Repository\StockMarketRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class MigrateStockMarketToEventSourceConsole extends Console
{
    protected static $defaultName = 'app:event-source:migrate-stock-market-event-source';

    /**
     * @var StockMarketRepository
     */
    private $stockMarketRepository;

    public function __construct(
        StockMarketRepository $stockMarketRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->stockMarketRepository = $stockMarketRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate stock markets data to event source');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $stockMarkets = $this->stockMarketRepository->findAll();

        $count = 0;
        $io->progressStart(count($stockMarkets));

        foreach ($stockMarkets as $stockMarket) {
            $this->bus->dispatch(
                new RegisterStockMarket(
                    $stockMarket->getName(),
                    $this->convertCurrencyEventSource($stockMarket->getCurrency()),
                    $stockMarket->getCountry(),
                    $stockMarket->getSymbol(),
                    $stockMarket->getYahooSymbol()
                )
            );

            $count++;
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success($count . ' stock markets were migrated successfully.');
    }
}
