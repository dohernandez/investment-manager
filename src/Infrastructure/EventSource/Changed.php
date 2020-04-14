<?php

namespace App\Infrastructure\EventSource;

use DateTime;
use Exception;

class Changed
{
    /**
     * @param string $eventId
     * @param string $eventName
     * @param mixed $payload
     * @param Metadata $metadata
     * @param string $aggregateType
     * @param string $aggregateId
     * @param int $aggregateVersion
     * @param DateTime|null $createdAt
     *
     * @throws Exception
     */
    public function __construct(
        string $eventId,
        string $eventName,
        $payload,
        Metadata $metadata,
        string $aggregateType,
        string $aggregateId,
        int $aggregateVersion,
        DateTime $createdAt = null
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->metadata = $metadata;
        $this->aggregateType = $aggregateType;
        $this->aggregateId = $aggregateId;
        $this->aggregateVersion = $aggregateVersion;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->payloadData = $payload;
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
     * Represent the domain event
     *
     * @var mixed
     */
    private $payload;

    public function getPayload()
    {
        return $this->payload;
    }

    public function setPayload($payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Represent a copy of the domain event. This attribute is used to migrate the payload
     * serialization in order to don't loose any event payload.
     *
     * @var mixed
     */
    private $payloadData;

    public function getPayloadData()
    {
        return $this->payloadData;
    }

    public function setPayloadData($payloadData): self
    {
        $this->payloadData = $payloadData;

        return $this;
    }

    /**
     * @var Metadata
     */
    private $metadata;

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function setMetadata(Metadata $metadata): self
    {
        $this->metadata = $metadata;

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
     * @var DateTime
     */
    private $createdAt;

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
