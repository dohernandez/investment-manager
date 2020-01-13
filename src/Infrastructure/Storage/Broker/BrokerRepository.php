<?php

namespace App\Infrastructure\Storage\Broker;

use App\Application\Broker\Repository\BrokerRepositoryInterface;
use App\Domain\Broker\Broker;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class BrokerRepository implements BrokerRepositoryInterface
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

    public function find(string $id): Broker
    {
        $changes = $this->eventSource->findEvents($id, Broker::class);

        $broker = (new Broker($id))->replay($changes);

        /** @var Broker $broker */
        $broker = $this->em->merge($broker);

        return $broker;
    }

    public function save(Broker $broker)
    {
        $this->eventSource->saveEvents($broker->getChanges());

        $this->em->persist($broker);
        $this->em->flush();
    }
}
