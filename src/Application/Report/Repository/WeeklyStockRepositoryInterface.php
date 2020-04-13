<?php

namespace App\Application\Report\Repository;


use App\Domain\Report\Weekly\Stock;

interface WeeklyStockRepositoryInterface
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
