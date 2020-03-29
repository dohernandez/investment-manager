<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Account;

interface AccountRepositoryInterface
{
    public function find(string $id): ?Account;

    public function findByAccountNo(string $accountNo): ?Account;
}
