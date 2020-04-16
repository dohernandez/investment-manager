<?php

namespace App\Application\Report\Repository;


use App\Domain\Report\Object\Stock;

interface StockRepositoryInterface
{
    /**
     * @param int $limit
     *
     * @return Stock[] The objects.
     */
    public function findAllMoversDaily(int $limit = 5): array;

    /**
     * @param int $limit
     *
     * @return Stock[] The objects.
     */
    public function findAllShakersDaily(int $limit = 5): array;
}
