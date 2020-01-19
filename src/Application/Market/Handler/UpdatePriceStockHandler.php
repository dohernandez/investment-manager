<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Event\StockPriceUpdated;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\StockPrice;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdatePriceStockHandler implements MessageHandlerInterface
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->stockRepository = $stockRepository;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(UpdateStockPrice $message)
    {
        $stock = $this->stockRepository->find($message->getId());

        $price = (new StockPrice())
            ->setPrice($message->getValue())
            ->setChangePrice($message->getChangePrice())
            ->setPeRatio($message->getPeRatio())
            ->setPreClose($message->getPreClose())
            ->setOpen($message->getOpen())
            ->setDayLow($message->getDayLow())
            ->setDayHigh($message->getDayHigh())
            ->setWeek52Low($message->getWeek52Low())
            ->setWeek52High($message->getWeek52High());

        if (!$stock->getPrice() || !$stock->getPrice()->equals($price) || true) {
            $stock->updatePrice($price);

            $this->stockRepository->save($stock);
        }

        return $stock;
    }
}

