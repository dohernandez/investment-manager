<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStock;
use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Command\UpdateStockWithPrice;
use App\Domain\Market\Stock;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class UpdateStockWithPriceHandler implements MessageHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        MessageBusInterface $bus
    ) {
        $this->bus = $bus;
    }

    public function __invoke(UpdateStockWithPrice $message)
    {
        $envelope = $this->bus->dispatch(
            new UpdateStock(
                $message->getId(),
                $message->getName(),
                $message->getYahooSymbol(),
                $message->getMarket(),
                $message->getValue(),
                $message->getDescription(),
                $message->getType(),
                $message->getSector(),
                $message->getIndustry()
            )
        );
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var Stock $stock */
        $stock = $handledStamp->getResult();

        $envelope = $this->bus->dispatch(
            new UpdateStockPrice(
                $stock->getId(),
                $message->getValue(),
                $message->getChangePrice(),
                $message->getPreClose(),
                $message->getOpen(),
                $message->getPeRatio(),
                $message->getDayLow(),
                $message->getDayHigh(),
                $message->getWeek52Low(),
                $message->getWeek52High()
            )
        );
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp->getResult();
    }
}
