<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Stock;

interface StockRepositoryInterface
{
    public function find(string $id): Stock;
}
