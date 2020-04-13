<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStockMarketPrice;
use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Domain\Market\MarketPrice;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateStockMarketPriceHandler implements MessageHandlerInterface
{
    /**
     * @var StockMarketRepositoryInterface
     */
    private $stockMarketRepository;

    public function __construct(
        StockMarketRepositoryInterface $stockMarketRepository
    ) {
        $this->stockMarketRepository = $stockMarketRepository;
    }

    public function __invoke(UpdateStockMarketPrice $message)
    {
        $stockMarket = $this->stockMarketRepository->find($message->getId());

        $price = (new MarketPrice())
            ->setPrice($message->getPrice())
            ->setChangePrice($message->getChangePrice())
            ->setPreClose($message->getPreClose())
            ->setData($message->getData())
            ->setWeek52Low($message->getWeek52Low())
            ->setWeek52High($message->getWeek52High());

        if (!$stockMarket->getPrice() || !$stockMarket->getPrice()->equals($price) || true) {
            $stockMarket->updatePrice($price);

            $this->stockMarketRepository->save($stockMarket);
        }

        return $stockMarket;
    }
}

