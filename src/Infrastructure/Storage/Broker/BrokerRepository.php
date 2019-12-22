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

        $this->em->getUnitOfWork()->registerManaged($broker, ['id' => $id], [
            'id' => $broker->getId(),
            'name' => $broker->getName(),
            'site' => $broker->getSite(),
            'currency' => $broker->getCurrency(),
            'createdAt' => $broker->getCreatedAt(),
            'updatedAt' => $broker->getUpdatedAt(),
        ]);

        return $broker;
    }

    public function save(Broker $broker)
    {
        $this->eventSource->saveEvents($broker->getChanges());

        $this->em->persist($broker);
        $this->em->flush();
    }
}
