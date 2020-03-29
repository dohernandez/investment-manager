<?php

namespace App\Infrastructure\Storage\Transfer;

use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\EventSource\EventSourceRepository;
use App\Infrastructure\Storage\Repository;
use Doctrine\ORM\EntityManagerInterface;

final class TransferRepository extends Repository implements TransferRepositoryInterface
{
    public function find(string $id): Transfer
    {
        return $this->load(Transfer::class, $id);
    }

    public function save(Transfer $transfer)
    {
        $this->store($transfer);
    }

    public function delete(Transfer $transfer)
    {
        $this->eventSource->saveEvents($transfer->getChanges());

        $this->em->remove($transfer);
        $this->em->flush();
    }
}
