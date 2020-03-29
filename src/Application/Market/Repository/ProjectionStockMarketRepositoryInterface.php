<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\StockMarket;

interface ProjectionStockMarketRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return StockMarket|null The broker.
     */
    public function find($id);

    /**
     * @return StockMarket[]
     */
    public function findAll();

    public function findBySymbol(string $symbol): ?StockMarket;
}
