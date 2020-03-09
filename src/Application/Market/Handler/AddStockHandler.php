<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\AddStock;
use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

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
        $name = $message->getName();
        $symbol = $message->getSymbol();
        $market = $message->getMarket();
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

        $dividendFrequency = $message->getDividendFrequency();

        $stock = Stock::add($name, $symbol, $market, $description, $type, $sector, $industry, $dividendFrequency);

        $this->stockRepository->save($stock);

        return $stock;
    }
}
