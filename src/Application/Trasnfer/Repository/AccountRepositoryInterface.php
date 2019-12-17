<?php

namespace App\Application\Trasnfer\Repository;

use App\Domain\Transfer\Account;

interface AccountRepositoryInterface
{
    public function find(string $id): Account;
}
