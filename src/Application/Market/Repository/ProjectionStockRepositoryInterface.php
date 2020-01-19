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
}
