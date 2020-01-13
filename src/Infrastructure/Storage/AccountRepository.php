<?php

namespace App\Infrastructure\Storage;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Domain\Account\AccountAggregate;
use App\Infrastructure\EventSource\AggregateRootRepository;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AccountRepository implements AccountRepositoryInterface
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

    public function find(string $id): Account
    {
        $changes = $this->eventSource->findEvents($id, Account::class);

        $account = (new Account($id))->replay($changes);

        /** @var Account $account */
        $account = $this->em->merge($account);

        return $account;
    }

    public function save(Account $account)
    {
        $this->eventSource->saveEvents($account->getChanges());

        $this->em->persist($account);
        $this->em->flush();
    }
}
