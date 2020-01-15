<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\AddStock;
use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AddStockHandler implements MessageHandlerInterface
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StockInfoRepositoryInterface
     */
    private $stockInfoRepository;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        StockInfoRepositoryInterface $stockInfoRepository
    ) {
        $this->stockRepository = $stockRepository;
        $this->stockInfoRepository = $stockInfoRepository;
    }

    public function __invoke(AddStock $message)
    {
        dump($message);
        $name = $message->getName();
        $symbol = $message->getSymbol();
        $market = $message->getMarket();
        $value = $message->getValue();
        $description = $message->getDescription();

        $type = $message->getType();
        if ($type) {
            $this->stockInfoRepository->save($type);
        }

        $sector = $message->getSector();
        if ($sector) {
            $this->stockInfoRepository->save($sector);
        }

        $industry = $message->getIndustry();
        if ($industry) {
            $this->stockInfoRepository->save($industry);
        }

        $stock = Stock::add($name, $symbol, $market, $value, $description, $type, $sector, $industry);

        $this->stockRepository->save($stock);

        return $stock;
    }
}
