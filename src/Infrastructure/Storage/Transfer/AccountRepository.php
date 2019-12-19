<?php

namespace App\Infrastructure\Storage\Transfer;

use App\Application\Trasnfer\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account as ProjectionAccount;
use App\Domain\Transfer\Account;
use App\Infrastructure\Storage\ProjectionAccountRepository;

final class AccountRepository implements AccountRepositoryInterface
{
    /**
     * @var ProjectionAccountRepository
     */
    private $projectionAccountRepository;

    public function __construct(ProjectionAccountRepository $projectionAccountRepository)
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
            $projectionAccount->getAccountNo()
        );
    }
}
