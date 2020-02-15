<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\OperationRepositoryInterface;
use App\Domain\Wallet\Operation;
use App\Domain\Wallet\Position;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Storage\Repository;

final class OperationRepository extends Repository implements OperationRepositoryInterface
{
    /**
     * @inherent
     */
    protected $dependencies = [
        'wallet' => Wallet::class,
    ];

    public function find(string $id): Operation
    {
        return $this->load(Operation::class, $id);
    }

    public function save(Operation $operation)
    {
        $this->store($operation);
    }

    public function delete(Operation $operation)
    {
        $this->eventSource->saveEvents($operation->getChanges());

        $this->em->remove($operation);
        $this->em->flush();
    }
}
