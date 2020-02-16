<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Wallet\Repository\StockRepositoryInterface;
use App\Domain\Market\StockMarket as ProjectionMarket;
use App\Domain\Market\Stock as ProjectionStock;
use App\Domain\Wallet\Market;
use App\Domain\Wallet\Stock;

final class StockRepository implements StockRepositoryInterface
{
    /**
     * @var ProjectionStockRepositoryInterface
     */
    private $projectionStockRepository;

    public function __construct(ProjectionStockRepositoryInterface $projectionStockRepository)
    {
        $this->projectionStockRepository = $projectionStockRepository;
    }

    public function find(string $id): Stock
    {
        $projectionStock = $this->projectionStockRepository->find($id);

        return $this->hydrate($projectionStock);
    }

    private function hydrate(ProjectionStock $projectionStock): Stock
    {
        $nextDividend = $projectionStock->getNextDividend() ? $projectionStock->getNextDividend()->getValue() : null;
        $nextDividendExDate = $projectionStock->getNextDividend() ? $projectionStock->getNextDividend()->getExDate() : null;
        $toPayDividend = $projectionStock->getToPayDividend() ? $projectionStock->getToPayDividend()->getValue() : null;
        $toPayDividendDate = $projectionStock->getToPayDividend() ? $projectionStock->getToPayDividend()->getExDate() : null;

        return new Stock(
            $projectionStock->getId(),
            $projectionStock->getName(),
            $projectionStock->getSymbol(),
            $this->hydrateMarket($projectionStock->getMarket()),
            $projectionStock->getPrice()->getPrice(),
            $nextDividend,
            $nextDividendExDate,
            $toPayDividend,
            $toPayDividendDate
        );
    }

    private function hydrateMarket(ProjectionMarket $projectionMarket): Market
    {
        return new Market(
            $projectionMarket->getId(),
            $projectionMarket->getName(),
            $projectionMarket->getSymbol()
        );
    }
}
