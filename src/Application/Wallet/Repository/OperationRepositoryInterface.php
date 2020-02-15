<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Operation;

interface OperationRepositoryInterface
{
    public function find(string $id): Operation;

    public function save(Operation $operation);

    public function delete(Operation $operation);
}
