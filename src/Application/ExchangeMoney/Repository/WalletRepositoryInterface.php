<?php

namespace App\Application\ExchangeMoney\Repository;

use App\Domain\ExchangeMoney\Wallet;

interface WalletRepositoryInterface
{
    /**
     * @return Wallet[]
     */
    public function findAll(): array;
}
