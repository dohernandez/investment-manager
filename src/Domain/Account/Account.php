<?php

namespace App\Domain\Account;

use App\Domain\Account\Event\AccountClosed;
use App\Domain\Account\Event\AccountCredited;
use App\Domain\Account\Event\AccountDebited;
use App\Domain\Account\Event\AccountOpened;
use App\Domain\Account\Exception\AccountClosedException;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\AggregateRootTypeTrait;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;

class Account extends AggregateRoot implements DataInterface
{
    use AggregateRootTypeTrait;
    use Data;

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

    /**
     * @var string
     */
    private $accountNo;

    public function getAccountNo(): string
    {
        return $this->accountNo;
    }

    /**
     * @var Money
     */
    private $balance;

    /**
     * @return Money
     */
    public function getBalance(): Money
    {
        return $this->balance;
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

    /**
     * @var bool
     */
    protected $isClosed;

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    public function getTitle()
    {
        return sprintf('%s - %s', $this->getName(), $this->getAccountNo());
    }

    public static function open(string $name, string $type, string $accountNo, Currency $currency): self
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(new AccountOpened($id, $name, $type, $accountNo, $currency));

        return $self;
    }

    public function withdraw(Money $money): self
    {
        if ($this->isClosed) {
            throw new AccountClosedException('Withdraw not possible, account closed');
        }

        if ($money->getValue() == 0) {
            return $this;
        }

        $this->recordChange(new AccountDebited($this->id, $money));

        return $this;
    }

    public function deposit(Money $money): self
    {
        if ($this->isClosed) {
            throw new AccountClosedException('Deposit not possible, account closed');
        }

        if ($money->getValue() == 0) {
            return $this;
        }

        $this->recordChange(new AccountCredited($this->id, $money));

        return $this;
    }

    public function close()
    {
        $this->recordChange(new AccountClosed($this->id));

        return $this;
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case AccountOpened::class:
                /** @var AccountOpened $event */
                $event = $changed->getPayload();

                $this->id = $changed->getAggregateId();
                $this->name = $event->getName();
                $this->type = $event->getType();
                $this->accountNo = $event->getAccountNo();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();
                $this->balance = new Money($event->getCurrency());
                $this->isClosed = false;

                break;

            case AccountCredited::class:
                /** @var AccountCredited $event */
                $event = $changed->getPayload();

                $this->balance = $this->balance->increase($event->getMoney());
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case AccountDebited::class:
                /** @var AccountDebited $event */
                $event = $changed->getPayload();

                $this->balance = $this->balance->decrease($event->getMoney());
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case AccountClosed::class:
                $this->isClosed = true;

                break;
        }
    }
}
