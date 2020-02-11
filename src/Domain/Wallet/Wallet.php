<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\WalletCreated;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\UUID;
use DateTime;

class Wallet extends AggregateRoot implements EventSourcedAggregateRoot
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
    private $slug;

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @var Broker
     */
    private $broker;

    public function getBroker(): Broker
    {
        return $this->broker;
    }

    /**
     * @var Account
     */
    private $account;

    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @var WalletMetadata
     */
    private $metadata;

    public function getMetadata(): WalletMetadata
    {
        return $this->metadata;
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

    public function getTitle(): string
    {
        return \sprintf('%s (%s)', $this->name, $this->slug);
    }

    public static function create(string $name, Broker $broker, Account $account, ?string $slug = null)
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $metadata = new WalletMetadata();
        $self->recordChange(
            new WalletCreated(
                $id,
                $name,
                $broker,
                $account,
                $metadata,
                $slug
            )
        );

        return $self;
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case WalletCreated::class:
                /** @var WalletCreated $event */
                $event = $changed->getPayload();

                $this->id = $event->getId();
                $this->name = $event->getName();
                $this->slug = $event->getSlug();

                $this->broker = $event->getBroker();
                $this->account = $event->getAccount();
                $this->metadata = $event->getMetadata();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;
        }
    }
}
