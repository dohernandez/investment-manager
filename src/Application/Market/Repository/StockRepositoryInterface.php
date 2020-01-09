<?php

namespace App\Application\Market\Repository;

use App\Domain\Market\Stock;

interface StockRepositoryInterface
{
    public function find(string $id): Stock;

    public function save(Stock $stock);
}
