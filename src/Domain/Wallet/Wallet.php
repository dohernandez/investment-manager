<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\WalletCreated;
use App\Domain\Wallet\Event\WalletInvestmentIncreased;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
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
     * @var string
     */
    private $accountId;

    /**
     * @var WalletBook
     */
    private $book;

    public function getBook(): WalletBook
    {
        return $this->book;
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

        $book = WalletBook::createWithInitialBalance($broker->getCurrency(), $account->getBalance());

        $self->recordChange(
            new WalletCreated(
                $id,
                $name,
                $broker,
                $account,
                $book,
                $slug
            )
        );

        return $self;
    }

    public function increaseInvestment(Money $invested)
    {
        $book = $this->getBook();

        $this->recordChange(
            new WalletInvestmentIncreased(
                $this->id,
                $book->getInvested()->increase($invested),
                $book->getCapital()->increase($invested),
                $book->getFunds()->increase($invested)
            )
        );

        return $this;
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
                $this->book = $event->getBook();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                $this->accountId = $this->account->getId();

                break;

            case WalletInvestmentIncreased::class:
                /** @var WalletInvestmentIncreased $event */
                $event = $changed->getPayload();

                $this->book->setInvested($event->getInvested());
                $this->book->setCapital($event->getCapital());
                $this->book->setFunds($event->getFunds());
        }
    }
}
