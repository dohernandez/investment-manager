<?php

namespace App\Infrastructure\Storage\ExchangeMoney;

use App\Application\ExchangeMoney\Repository\MarketRepositoryInterface;
use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Domain\ExchangeMoney\Market;

final class MarketRepository implements MarketRepositoryInterface
{
    /**
     * @var ProjectionStockMarketRepositoryInterface
     */
    private $projectionStockMarketRepository;

    public function __construct(ProjectionStockMarketRepositoryInterface $projectionStockMarketRepository)
    {
        $this->projectionStockMarketRepository = $projectionStockMarketRepository;
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        $projectionMarkets = $this->projectionStockMarketRepository->findAll();

        if (empty($projectionMarkets)) {
            return [];
        }

        $markets = [];

        foreach ($projectionMarkets as $projectionMarket) {
            $markets[] = new Market($projectionMarket->getCurrency());
        }

        return $markets;
    }
}
