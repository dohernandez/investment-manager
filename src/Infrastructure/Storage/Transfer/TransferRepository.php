<?php

namespace App\Infrastructure\Storage\Transfer;

use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TransferRepository implements TransferRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventSourceRepository
     */
    private $eventSource;

    public function __construct(EntityManagerInterface $em, EventSourceRepository $eventSource)
    {
        $this->em = $em;
        $this->eventSource = $eventSource;
    }

    public function find(string $id): Transfer
    {
        $changes = $this->eventSource->findEvents($id, Transfer::class);

        $transfer = (new Transfer($id))->replay($changes);

        $this->em->getUnitOfWork()->registerManaged($transfer, ['id' => $id], [
            'id' => $transfer->getId(),
            'beneficiaryParty' => $transfer->getBeneficiaryParty(),
            'debtorParty' => $transfer->getDebtorParty(),
            'amount' => $transfer->getAmount(),
            'date' => $transfer->getDate(),
            'createdAt' => $transfer->getCreatedAt(),
            'updatedAt' => $transfer->getUpdatedAt(),
        ]);

        return $transfer;
    }

    public function save(Transfer $transfer)
    {
        $this->eventSource->saveEvents($transfer->getChanges());

        $this->em->persist($transfer);
        $this->em->flush();
    }

    public function delete(Transfer $transfer)
    {
        $this->eventSource->saveEvents($transfer->getChanges());

        $this->em->remove($transfer);
        $this->em->flush();
    }
}
