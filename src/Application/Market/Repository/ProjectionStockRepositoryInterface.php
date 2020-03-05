<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\Stock;

interface ProjectionStockRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return Stock|null The stock.
     */
    public function find($id);

    /**
     * @return Stock[] The objects.
     */
    public function findAll();

    /**
     * @param string $query
     * @param int $limit
     *
     * @return Stock[] The objects.
     */
    public function findAllMatching(string $query, int $limit = 5): array;

    public function findBySymbol(string $symbol): ?Stock;

    /**
     * @return Stock[] The objects.
     */
    public function findAllListed(): array;
}
