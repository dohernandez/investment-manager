<?php

namespace App\Domain\Market;

use App\Domain\Market\Event\StockInfoAdded;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\UUID;
use DateTime;

class StockInfo extends AggregateRoot implements EventSourcedAggregateRoot
{
    public const TYPE = 'type';
    public const SECTOR = 'sector';
    public const INDUSTRY = 'industry';

    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $type;

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->name;
    }

    public static function add(string $name, string $type): self
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(new StockInfoAdded($id, $name, $type));

        return $self;
    }

    public static function createTemporary(string $name, string $type): self
    {
        $self = new static('');

        $self->name = $name;
        $self->type = $type;

        return $self;
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case StockInfoAdded::class:
                /** @var StockInfoAdded $event */
                $event = $changed->getPayload();

                $this->id = $changed->getAggregateId();
                $this->name = $event->getName();
                $this->type = $event->getType();
                $this->createdAt = $changed->getCreatedAt();

                break;
        }
    }

    /**
     * @var DateTime
     */
    private $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
