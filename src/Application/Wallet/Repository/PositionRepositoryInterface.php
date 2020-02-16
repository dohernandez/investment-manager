<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Position;

interface PositionRepositoryInterface
{
    public function find(string $id): Position;

    public function save(Position $position);
}
