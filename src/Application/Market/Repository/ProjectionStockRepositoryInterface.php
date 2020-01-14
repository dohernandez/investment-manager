<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\Stock;

interface ProjectionStockRepositoryInterface
{
    /**
     * @return Stock[] The objects.
     */
    public function findAll();
}
