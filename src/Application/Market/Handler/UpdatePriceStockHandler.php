<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStockPrice;
use App\Application\Market\Event\StockPriceUpdated;
use App\Application\Market\Repository\StockRepositoryInterface;
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

        $oldPrice = $stock->getMetadata()->getPrice();

        $stock->updatePrice(
            $message->getValue(),
            $message->getPreClose(),
            $message->getOpen(),
            $message->getPeRatio(),
            $message->getDayLow(),
            $message->getDayHigh(),
            $message->getWeek52Low(),
            $message->getWeek52High()
        );

        dump($stock);
        $this->stockRepository->save($stock);

        /** Manually dispatch the application event.
         * This is because update price does not generate domain event hence it is not stored in the event source,
         * but we still want to trigger event.
         */
        $event = new StockPriceUpdated($stock->getId(), $stock->getMetadata()->getPrice(), $oldPrice);
        $this->dispatcher->dispatch($event);

        return $stock;
    }
}

