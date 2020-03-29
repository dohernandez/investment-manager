<?php

namespace App\Presentation\Console;

use App\Application\Market\Command\AddStock;
use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Domain\Market\StockDividend;
use App\Domain\Market\StockInfo;
use App\Repository\StockRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class MigrateStockToEventSourceConsole extends Console
{
    protected static $defaultName = 'app:event-source:migrate-stock-event-source';

    /**
     * @var StockRepository
     */
    private $stockRepository;

    /**
     * @var ProjectionStockMarketRepositoryInterface
     */
    private $stockMarketRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProjectionStockInfoRepositoryInterface
     */
    private $stockInfoRepository;

    public function __construct(
        StockRepository $stockRepository,
        MessageBusInterface $bus,
        ProjectionStockMarketRepositoryInterface $stockMarketRepository,
        LoggerInterface $logger,
        ProjectionStockInfoRepositoryInterface $stockInfoRepository
    ) {
        parent::__construct($bus);

        $this->stockRepository = $stockRepository;
        $this->stockMarketRepository = $stockMarketRepository;
        $this->logger = $logger;
        $this->stockInfoRepository = $stockInfoRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate stocks data to event source');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $stocks = $this->stockRepository->findAll();

        $count = 0;
        $io->progressStart(count($stocks));

        foreach ($stocks as $stock) {
            $stockMarket = $this->stockMarketRepository->findBySymbol($stock->getMarket()->getSymbol());
            if (!$stockMarket) {
                $this->logger->error(
                    'Can not migrate stock due to market not found',
                    [
                        'stock_id'            => $stock->getId(),
                        'stock_symbol'        => $stock->getSymbol(),
                        'stock_market_symbol' => $stock->getMarket()->getSymbol(),
                    ]
                );

                continue;
            }

            $type = null;
            if ($stock->getType() && $stock->getType()->getName()) {
                $type = $this->stockInfoRepository->findByName($stock->getType()->getName());
                if (!$type) {
                    $this->logger->warning(
                        'Stock info type not found',
                        [
                            'stock_id'        => $stock->getId(),
                            'stock_symbol'    => $stock->getSymbol(),
                            'stock_info_name' => $stock->getType()->getName(),
                        ]
                    );

                    $type = StockInfo::add($stock->getType()->getName(), $stock->getType()->getType());

                    $this->logger->warning(
                        'Stock info type added',
                        [
                            'stock_symbol'    => $stock->getSymbol(),
                            'stock_info_name' => $stock->getType()->getName(),
                        ]
                    );
                }
            }

            $sector = null;
            if ($stock->getSector() && $stock->getSector()->getName()) {
                $sector = $this->stockInfoRepository->findByName($stock->getSector()->getName());
                if (!$sector) {
                    $this->logger->warning(
                        'Stock info sector not found',
                        [
                            'stock_id'        => $stock->getId(),
                            'stock_symbol'    => $stock->getSymbol(),
                            'stock_info_name' => $stock->getSector()->getName(),
                        ]
                    );

                    $sector = StockInfo::add($stock->getSector()->getName(), $stock->getSector()->getType());

                    $this->logger->warning(
                        'Stock info sector added',
                        [
                            'stock_symbol'    => $stock->getSymbol(),
                            'stock_info_name' => $stock->getSector()->getName(),
                        ]
                    );
                }
            }

            $industry = null;
            if ($stock->getIndustry() && $stock->getIndustry()->getName()) {
                $industry = $this->stockInfoRepository->findByName($stock->getIndustry()->getName());
                if (!$industry) {
                    $this->logger->warning(
                        'Stock info industry not found',
                        [
                            'stock_id'        => $stock->getId(),
                            'stock_symbol'    => $stock->getSymbol(),
                            'stock_info_name' => $stock->getIndustry()->getName(),
                        ]
                    );

                    $industry = StockInfo::add($stock->getIndustry()->getName(), $stock->getIndustry()->getType());

                    $this->logger->warning(
                        'Stock info industry added',
                        [
                            'stock_symbol'    => $stock->getSymbol(),
                            'stock_info_name' => $stock->getIndustry()->getName(),
                        ]
                    );
                }
            }

            $this->bus->dispatch(
                new AddStock(
                    $stock->getName(),
                    $stock->getSymbol(),
                    null,
                    $stockMarket,
                    $stock->getDescription(),
                    $type,
                    $sector,
                    $industry,
                    StockDividend::FREQUENCY_QUARTERLY
                )
            );

            $count++;
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success($count . ' stocks were migrated successfully.');
    }
}
