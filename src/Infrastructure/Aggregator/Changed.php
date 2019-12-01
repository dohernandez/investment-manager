<?php

namespace App\Infrastructure\Aggregator;

class Changed
{
    public function __construct(
        string $eventId,
        string $eventName,
        Event $payload,
        Metadata $metadata,
        string $aggregateType,
        string $aggregateId,
        int $aggregateVersion,
        \DateTimeImmutable $createdAt
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->metadata = $metadata;
        $this->aggregateType = $aggregateType;
        $this->aggregateId = $aggregateId;
        $this->aggregateVersion = $aggregateVersion;
        $this->createdAt = $createdAt;
    }

    /**
     * @var int
     */
    private $no;

    /**
     * @return int
     */
    public function getNo(): int
    {
        return $this->no;
    }

    /**
     * @var string
     */
    private $eventId;

    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * @var string
     */
    private $eventName;

    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @var Event
     */
    private $payload;

    public function getPayload(): Event
    {
        return $this->payload;
    }

    /**
     * @var Metadata
     */
    private $metadata;

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function setMetadata(string $key, $value): self
    {
        $this->metadata;

        return $this;
    }

    /**
     * @var string
     */
    private $aggregateType;

    public function getAggregateType(): string
    {
        return $this->aggregateType;
    }

    /**
     * @var string
     */
    private $aggregateId;

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @var int
     */
    private $aggregateVersion;

    public function getAggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
