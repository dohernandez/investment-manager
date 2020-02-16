<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Position;

interface ProjectionPositionRepositoryInterface
{
    public function findByStock(string $stockId): ?Position;
}
