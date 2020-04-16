<?php

namespace App\Application\Report\Repository;

use App\Domain\Report\Wallet\Position;

interface PositionRepositoryInterface
{
    /**
     * @param string $walletId
     *
     * @return Position[]
     */
    public function findAllOpenByWallet(string $walletId): array;
}
