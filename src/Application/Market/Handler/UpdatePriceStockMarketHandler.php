<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStockMarketPrice;
use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Domain\Market\StockMarketPrice;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdatePriceStockMarketHandler implements MessageHandlerInterface
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

        $price = (new StockMarketPrice())
            ->setPrice($message->getValue())
            ->setChangePrice($message->getChangePrice())
            ->setPreClose($message->getPreClose())
            ->setOpen($message->getOpen())
            ->setDayLow($message->getDayLow())
            ->setDayHigh($message->getDayHigh())
            ->setWeek52Low($message->getWeek52Low())
            ->setWeek52High($message->getWeek52High());

        if (!$stockMarket->getPrice() || !$stockMarket->getPrice()->equals($price) || true) {
            $stockMarket->updatePrice($price);

            $this->stockMarketRepository->save($stockMarket);
        }

        return $stockMarket;
    }
}

