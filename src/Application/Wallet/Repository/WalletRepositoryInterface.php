<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Wallet;

interface WalletRepositoryInterface
{
    public function find(string $id): Wallet;

    public function save(Wallet $wallet);

    public function delete(Wallet $wallet);
}
