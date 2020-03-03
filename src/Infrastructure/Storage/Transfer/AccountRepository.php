<?php

namespace App\Infrastructure\Storage\Transfer;

use App\Application\Account\Repository\ProjectionAccountRepositoryInterface;
use App\Application\Transfer\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account as ProjectionAccount;
use App\Domain\Transfer\Account;

final class AccountRepository implements AccountRepositoryInterface
{
    /**
     * @var ProjectionAccountRepositoryInterface
     */
    private $projectionAccountRepository;

    public function __construct(ProjectionAccountRepositoryInterface $projectionAccountRepository)
    {
        $this->projectionAccountRepository = $projectionAccountRepository;
    }

    public function find(string $id): Account
    {
        return $this->hydrate(
            $this->projectionAccountRepository->find($id)
        );
    }

    public function hydrate(ProjectionAccount $projectionAccount): Account
    {
        return new Account(
            $projectionAccount->getId(),
            $projectionAccount->getName(),
            $projectionAccount->getAccountNo()
        );
    }

    public function findByAccountNo(string $accountNo): Account
    {
        return $this->hydrate(
            $this->projectionAccountRepository->findByAccountNo($accountNo)
        );
    }
}
