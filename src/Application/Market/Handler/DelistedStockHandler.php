<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\DelistedStock;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DelistedStockHandler implements MessageHandlerInterface
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    public function __construct(
        StockRepositoryInterface $stockRepository
    ) {
        $this->stockRepository = $stockRepository;
    }

    public function __invoke(DelistedStock $message)
    {
        $stock = $this->stockRepository->find($message->getId());

        if (!$stock) {
            throw new NotFoundException('Stock not found', [
                'stock_id' => $message->getId()
            ]);
        }

        $stock->delisted();
        $this->stockRepository->save($stock);

        return $stock;
    }
}
