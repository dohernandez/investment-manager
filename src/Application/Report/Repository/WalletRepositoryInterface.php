<?php

namespace App\Application\Report\Repository;

use App\Domain\Report\Wallet\Wallet;

interface WalletRepositoryInterface
{
    public function find(string $id): Wallet;
}
