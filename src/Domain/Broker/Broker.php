<?php

namespace App\Domain\Broker;

use App\Domain\Broker\Event\BrokerChanged;
use App\Domain\Broker\Event\BrokerRegistered;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\UUID;
use DateTime;

class Broker extends AggregateRoot implements EventSourcedAggregateRoot
{
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
    private $site;

    public function getSite(): string
    {
        return $this->site;
    }

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @var DateTime
     */
    private $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @var DateTime
     */
    private $updatedAt;

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public static function register(string $name, string $site, Currency $currency): self
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(new BrokerRegistered($id, $name, $site, $currency));

        return $self;
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function change(string $name, string $site, Currency $currency)
    {
        $this->recordChange(new BrokerChanged($this->getId(), $name, $site, $currency));
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case BrokerRegistered::class:
                /** @var BrokerRegistered $event */
                $event = $changed->getPayload();

                $this->id = $changed->getAggregateId();
                $this->name = $event->getName();
                $this->site = $event->getSite();
                $this->currency = $event->getCurrency();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;
            case BrokerChanged::class:
                /** @var BrokerChanged $event */
                $event = $changed->getPayload();

                $this->name = $event->getName();
                $this->site = $event->getSite();
                $this->currency = $event->getCurrency();
                $this->updatedAt = $changed->getCreatedAt();

                break;
        }
    }
}
