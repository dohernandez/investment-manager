<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\StockInfo;

interface ProjectionStockInfoRepositoryInterface
{
    public function findByName(string $name): ?StockInfo;

    /**
     * @param string $type
     * @param string $query
     * @param int $limit
     *
     * @return StockInfo[]
     */
    public function findAllTypeMatching(string $type, string $query, int $limit = 5): array;

    /**
     * @param string $query
     * @param int $limit
     *
     * @return StockInfo[]
     */
    public function findAllMatching(string $query, int $limit = 5): array;

    /**
     * @return StockInfo[]
     */
    public function findAll();
}
