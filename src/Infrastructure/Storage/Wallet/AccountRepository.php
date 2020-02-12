<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Account\Repository\ProjectionAccountRepositoryInterface;
use App\Application\Wallet\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account as ProjectionAccount;
use App\Domain\Wallet\Account;

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
        $projectionAccount = $this->projectionAccountRepository->find($id);

        return $this->hydrate($projectionAccount);
    }

    public function hydrate(ProjectionAccount $projectionAccount): Account
    {
        return new Account(
            $projectionAccount->getId(),
            $projectionAccount->getName(),
            $projectionAccount->getAccountNo(),
            $projectionAccount->getBalance()
        );
    }
}
