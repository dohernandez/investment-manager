<?php

namespace App\Infrastructure\Storage;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Domain\Account\AccountAggregate;
use App\Infrastructure\EventSource\AggregateRootRepository;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AccountRepository extends Repository implements AccountRepositoryInterface
{
    public function find(string $id): Account
    {
        return $this->load(Account::class, $id);
    }

    public function save(Account $account)
    {
        $this->store($account);
    }
}
