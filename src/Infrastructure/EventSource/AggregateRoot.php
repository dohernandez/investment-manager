<?php

namespace App\Infrastructure\EventSource;

use Doctrine\Common\Collections\ArrayCollection;
use App\Infrastructure\UUID;

abstract class AggregateRoot implements EventSourcedAggregateRoot
{
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->changes = new ArrayCollection();
        $this->version = 0;
    }

    /**
     * @var string
     */
    protected $id;

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @var int
     */
    private $version;

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @var ArrayCollection
     */
    private $changes;

    public function getChanges(): ArrayCollection
    {
        return $this->changes;
    }

    public function recordChange($event): void
    {
        if (empty($this->id)) {
            throw new MissingAggregateIDException();
        }

        $this->version++;

        $aggregateChanged = new Changed(
            UUID\Generator::generate(),
            get_class($event),
            $event,
            new Metadata(),
            get_class($this),
            $this->id,
            $this->getVersion()
        );

        $this->changes->add($aggregateChanged);

        $this->apply($aggregateChanged);
    }

    abstract protected function apply(Changed $changed);

    /**
     * @inheritDoc
     */
    public static function reconstitute(string $id, array $changes)
    {
        $self = new static($id);

        foreach ($changes as $changed) {
            $self->version = $changed->getAggregateVersion();
            $self->changes->add($changed);

            $self->apply($changed);
        }

        return $self;
    }
}
