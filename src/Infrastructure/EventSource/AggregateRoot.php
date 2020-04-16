<?php

namespace App\Infrastructure\EventSource;

use App\Domain\Market\Event\StockPriceUpdated;
use App\Domain\Market\Stock;
use ArrayIterator;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use App\Infrastructure\UUID;
use ReflectionClass;

abstract class AggregateRoot implements EventSourcedAggregateRoot
{
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->changes = new ArrayCollection();
    }

    /**
     * @var string
     */
    protected $id;

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @var int
     */
    private $version = 0;

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @var ArrayCollection
     */
    private $changes;

    public function getChanges(): ?ArrayCollection
    {
        return $this->changes;
    }

    public function setChanges(ArrayCollection $changes): self
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * @param $event
     *
     * @return self
     * @throws \Exception
     */
    public function recordChange($event)
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
            $this->getAggregateType(),
            $this->id,
            $this->getVersion()
        );

        $this->changes->add($aggregateChanged);

        $this->apply($aggregateChanged);

        return $this;
    }

    /**
     * @param Changed $aggregateChanged
     * @param $event
     *
     * @param string $updatedAt
     *
     * @return self
     * @throws \ReflectionException
     */
    public function replaceChangedPayload(Changed $aggregateChanged, $event, DateTime $updatedAt = null)
    {
        if (empty($this->id)) {
            throw new MissingAggregateIDException();
        }

        $updatedAt = $updatedAt ?? new DateTime();

        $aggregateChanged->setPayload($event);
        $aggregateChanged->setMetadata($aggregateChanged->getMetadata()->changeUpdatedAt($updatedAt));

        $this->apply($aggregateChanged);

        return $this;
    }

    abstract protected function apply(Changed $changed);

    /**
     * @inheritDoc
     */
    public function replay(array $changes)
    {
        if (!$this->changes) {
            $this->changes = new ArrayCollection();
        }

        foreach ($changes as $changed) {
            $this->version = $changed->getAggregateVersion();
            $this->changes->add($changed);

            $this->apply($changed);
        }

        return $this;
    }

    protected function findIfLastChangeHappenedIsName(
        ?string $type = null
    ): ?Changed {
        if ($this->getChanges()->isEmpty()) {
            return null;
        }

        $iterator = new ArrayIterator($this->getChanges()->getValues());
        // define ordering closure, using preferred comparison method/field
        $iterator->uasort(
            function ($first, $second) {
                /** @var Changed $first */
                /** @var Changed $second */
                return $first->getAggregateVersion() < $second->getAggregateVersion() ? 1 : -1;
            }
        );

        // reset current to the beginning
        $iterator->rewind();
        /** @var Changed $changed */
        $changed = $iterator->current();
        return $changed->getEventName() === $type ? $changed : null;
    }

    /**
     * This function is required in the aggregate root because when the object is merged with in
     * the entity manage, the class is changed to a Proxies\__CG__ giving a wrong aggregate type.
     */
     abstract public function getAggregateType(): string;
}
