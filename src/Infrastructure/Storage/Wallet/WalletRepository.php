<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class WalletRepository implements WalletRepositoryInterface
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

    public function find(string $id): Wallet
    {
        $changes = $this->eventSource->findEvents($id, Wallet::class);

        $wallet = (new Wallet($id))->replay($changes);

        /** @var Wallet $wallet */
        $wallet = $this->em->merge($wallet);

        return $wallet;
    }

    public function save(Wallet $wallet)
    {
        $this->eventSource->saveEvents($wallet->getChanges());

        $this->em->persist($wallet);
        $this->em->flush();
    }

    public function delete(Wallet $wallet)
    {
        $this->eventSource->saveEvents($wallet->getChanges());

        $this->em->remove($wallet);
        $this->em->flush();
    }
}
