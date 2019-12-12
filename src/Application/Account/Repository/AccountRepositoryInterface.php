<?php

namespace App\Application\Account\Repository;

use App\Domain\Account\Account;

interface AccountRepositoryInterface
{
    public function find(string $id): Account;

    public function save(Account $account);

    public function remove(Account $account);
}
