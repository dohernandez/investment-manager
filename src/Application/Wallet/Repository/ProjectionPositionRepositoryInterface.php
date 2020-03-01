<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Position;

interface ProjectionPositionRepositoryInterface
{
    public function findByStock(string $walletId, string $stockId, ?string $status = null): ?Position;

    /**
     * @param string $walletId
     * @param string $status
     *
     * @return Position[]
     */
    public function findAllByStatus(string $walletId, string $status): array;
}
