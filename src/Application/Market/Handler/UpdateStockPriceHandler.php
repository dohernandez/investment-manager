<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\MarketPrice;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateStockPriceHandler implements MessageHandlerInterface
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

    public function __invoke(UpdateStockPrice $message)
    {
        $stock = $this->stockRepository->find($message->getId());

        $price = (new MarketPrice())
            ->setPrice($message->getPrice())
            ->setChangePrice($message->getChangePrice())
            ->setPeRatio($message->getPeRatio())
            ->setPreClose($message->getPreClose())
            ->setData($message->getData())
            ->setWeek52Low($message->getWeek52Low())
            ->setWeek52High($message->getWeek52High());

        if (!$stock->getPrice() || !$stock->getPrice()->equals($price) || true) {
            $stock->updatePrice($price);

            $this->stockRepository->save($stock);
        }

        return $stock;
    }
}

