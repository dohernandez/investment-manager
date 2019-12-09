<?php

namespace App\Infrastructure\Aggregator;

use Doctrine\Common\Collections\ArrayCollection;
use App\Infrastructure\UUID;

abstract class AggregateRoot
{
    public function __construct()
    {
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
     * @param Changed[] $historyChanges
     */
    public function replay(array $historyChanges): self
    {
        foreach ($historyChanges as $changed) {
            $this->version = $changed->getAggregateVersion();
            $this->changes->add($changed);

            $this->apply($changed);
        }

        return $this;
    }

    public function getLastChanged(): Changed
    {
        return $this->changes->last();
    }
}
