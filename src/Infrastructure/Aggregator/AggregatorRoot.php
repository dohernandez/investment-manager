<?php

namespace App\Infrastructure\Aggregator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use function uniqid;

abstract class AggregatorRoot
{
    /**
     * @var int
     */
    private $version;

    /**
     * @var ArrayCollection
     */
    private $changes;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $bus)
    {
        $this->changes = new ArrayCollection();
        $this->version = 0;

        $this->em = $em;
        $this->bus = $bus;
    }

    public function recordChange(Event $event): void
    {
        $this->version ++;

        $aggregateChanged = new Changed(
            uniqid(),
            $event->getName(),
            $event,
            new Metadata(),
            $this->getType(),
            uniqid(),
            $this->getVersion(),
            new \DateTimeImmutable()
        );

        $this->changes->add($aggregateChanged);

        $this->Apply($aggregateChanged);

        $this->em->persist($aggregateChanged);
        $this->em->flush();

        $this->bus->dispatch($event);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    abstract public function getType();

    /**
     * Apply updates the state of the aggregateRoot based on the recorded event.
     *
     * @param Changed $event
     */
    abstract public function Apply(Changed $event): void;
}
