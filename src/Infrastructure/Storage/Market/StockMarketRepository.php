<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Domain\Market\StockMarket;
use App\Infrastructure\EventSource\EventSourceRepository;
use App\Infrastructure\Storage\Repository;
use Doctrine\ORM\EntityManagerInterface;

final class StockMarketRepository extends Repository implements StockMarketRepositoryInterface
{
    public function find(string $id): StockMarket
    {
        return $this->load(StockMarket::class, $id);
    }

    public function save(StockMarket $market)
    {
        $this->store($market);
    }
}
