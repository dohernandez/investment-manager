<?php

namespace App\Infrastructure\EventSource;

use App\Domain\Market\Event\StockPriceUpdated;
use App\Domain\Market\Stock;
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
        $this->version = 0;
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
    private $version;

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
            get_class($this),
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
        foreach ($changes as $changed) {
            $this->version = $changed->getAggregateVersion();
            $this->changes->add($changed);

            $this->apply($changed);
        }

        return $this;
    }

    protected function findFirstChangeHappenedDateAt(
        DateTime $dateAt,
        ?string $type = null,
        string $method = 'getUpdatedAt'
    ): ?Changed {
        $changes = ($type) ? $this->getChanges()->filter(
            function (Changed $changed) use ($type) {
                if ($changed->getEventName() == $type) {
                    return true;
                }

                return false;
            }
        ) : $this->getChanges();

        $changed = null;

        /** @var Changed $change */
        foreach ($changes as $change) {
            /** @var StockPriceUpdated $payload */
            $payload = $change->getPayload();

            if (!\method_exists($payload, $method) || !$payload->$method()) {
                continue;
            }

            if (
                $payload->$method()->format('Y') === $dateAt->format('Y') &&
                $payload->$method()->format('z') === $dateAt->format('z')
            ) {
                $changed = $change;

                break;
            }
        }

        return $changed;
    }
}
