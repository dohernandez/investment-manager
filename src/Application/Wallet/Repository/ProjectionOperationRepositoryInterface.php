<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Operation;

interface ProjectionOperationRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return Operation|null The operation.
     */
    public function find($id);

    public function findAllByWallet(string $walletId): array;
}
