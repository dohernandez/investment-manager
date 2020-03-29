<?php

namespace App\Application\ExchangeMoney\Repository;

use App\Domain\ExchangeMoney\Market;

interface MarketRepositoryInterface
{
    /**
     * @return Market[]
     */
    public function findAll(): array;
}
