<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\SyncStockDividends;
use App\Application\Market\Decorator\StockDividendsDecorateInterface;
use App\Application\Market\Repository\StockDividendRepositoryInterface;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Application\Market\Service\StockDividendsServiceInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SyncStockDividendsHandler implements MessageHandlerInterface
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StockDividendRepositoryInterface
     */
    private $stockDividendsDecorate;

    /**
     * @var StockDividendsServiceInterface
     */
    private $stockDividendsService;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        StockDividendsDecorateInterface $stockDividendsDecorate,
        StockDividendsServiceInterface $stockDividendsService
    ) {
        $this->stockRepository = $stockRepository;
        $this->stockDividendsDecorate = $stockDividendsDecorate;
        $this->stockDividendsService = $stockDividendsService;
    }

    public function __invoke(SyncStockDividends $message)
    {
        $stock = $this->stockRepository->find($message->getId());

       $this->stockDividendsDecorate->decorate($stock);

       $stockDividends = $this->stockDividendsService->getStockDividends($stock);

       $stock->syncDividends($stockDividends);

       $this->stockRepository->save($stock);
    }
}
