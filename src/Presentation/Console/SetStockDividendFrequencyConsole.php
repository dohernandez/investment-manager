<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\UpdateStock;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\StockDividend;
use App\Infrastructure\Storage\Console\ConsoleStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class SetStockDividendFrequencyConsole extends Console
{
    protected static $defaultName = 'app:event-source:set-stock-dividend-frequency';

    /**
     * @var ConsoleStockRepository
     */
    private $consoleStockRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        ConsoleStockRepository $consoleStockRepository,
        StockRepositoryInterface $stockRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ) {
        parent::__construct($bus);

        $this->consoleStockRepository = $consoleStockRepository;
        $this->stockRepository = $stockRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Set dividend frequency to stock does not have.')
            ->addOption(
                'frequency',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Stock dividend frequency. Options ["monthly", "quarterly", "yearly"]. Default: quarterly',
                'quarterly'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $dividendFrequency = $input->getOption('frequency');
        switch ($dividendFrequency) {
            case 'monthly':
                $dividendFrequency = StockDividend::FREQUENCY_MONTHlY;

                break;
            case 'quarterly':
                $dividendFrequency = StockDividend::FREQUENCY_QUARTERLY;

                break;
            case 'yearly':
                $dividendFrequency = StockDividend::FREQUENCY_YEARLY;

                break;
            default:
                throw new InvalidArgumentException(sprintf('Frequency not supported [%s]', $dividendFrequency));
        }

        $stocks = $this->consoleStockRepository->findAllListed();
        $io->progressStart(count($stocks));

        $this->em->transactional(function () use ($stocks, $dividendFrequency, $io) {
            foreach ($stocks as $stock) {
                $stock = $this->stockRepository->find($stock['id']);

                $this->bus->dispatch(
                    new UpdateStock(
                        $stock->getId(),
                        $stock->getName(),
                        $stock->getMetadata()->getYahooSymbol(),
                        $stock->getMarket(),
                        $stock->getDescription(),
                        $stock->getType(),
                        $stock->getSector(),
                        $stock->getIndustry(),
                            $stock->getMetadata()->getDividendFrequency() ?? $dividendFrequency
                    )
                );

                $io->progressAdvance();
            }
        });

        $io->progressFinish();

        $io->success('History dividend updated successfully.');
    }
}
