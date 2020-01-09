<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\AddStock;
use App\Application\Market\Command\AddStockInfo;
use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockInfo;
use App\Infrastructure\Money\Money;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class AddStockHandler implements MessageHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var ProjectionStockMarketRepositoryInterface
     */
    private $projectionStockMarketRepository;

    /**
     * @var ProjectionStockInfoRepositoryInterface
     */
    private $projectionStockInfoRepository;

    public function __construct(
        MessageBusInterface $bus,
        StockRepositoryInterface $stockRepository,
        ProjectionStockMarketRepositoryInterface $projectionStockMarketRepository,
        ProjectionStockInfoRepositoryInterface $projectionStockInfoRepository
    ) {
        $this->bus = $bus;
        $this->stockRepository = $stockRepository;
        $this->projectionStockMarketRepository = $projectionStockMarketRepository;
        $this->projectionStockInfoRepository = $projectionStockInfoRepository;
    }

    public function __invoke(AddStock $message)
    {
        $name = $message->getName();
        $symbol = $message->getSymbol();
        $market = $this->projectionStockMarketRepository->find($message->getMarketId());
        $value = new Money($market->getCurrency(), $message->getValue());
        $description = $message->getDescription();

        $type = $this->findOrCreateStockInfo($message->getType(), StockInfo::TYPE);
        $sector = $this->findOrCreateStockInfo($message->getSector(), StockInfo::SECTOR);
        $industry = $this->findOrCreateStockInfo($message->getIndustry(), StockInfo::INDUSTRY);

        $stock = Stock::add($name, $symbol, $market, $value, $description, $type, $sector, $industry);

        $this->stockRepository->save($stock);

        return $stock;
    }

    private function findOrCreateStockInfo(string $stockInfoName, $type): ?StockInfo
    {
        if ($stockInfoName === null) {
            return null;
        }

        try {
            $stockInfo = $this->projectionStockInfoRepository->findByName($stockInfoName);
        } catch (\Exception $e) {
            $envelope = $this->bus->dispatch(new AddStockInfo($stockInfoName, $type));

            // get the value that was returned by the last message handler
            $handledStamp = $envelope->last(HandledStamp::class);
            $stockInfo = $handledStamp->getResult();
        }

        return $stockInfo;
    }
}
