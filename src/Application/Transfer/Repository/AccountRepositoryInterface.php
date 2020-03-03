<?php

namespace App\Application\Transfer\Repository;

use App\Domain\Transfer\Account;

interface AccountRepositoryInterface
{
    public function find(string $id): ?Account;

    public function findByAccountNo(string $accountNo): ?Account;
}
