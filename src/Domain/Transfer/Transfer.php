<?php

namespace App\Domain\Transfer;

use App\Domain\Transfer\Exception\TransferRemovedException;
use App\Domain\Transfer\Event\TransferChanged;
use App\Domain\Transfer\Event\TransferRegistered;
use App\Domain\Transfer\Event\TransferRemoved;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;

final class Transfer extends AggregateRoot implements EventSourcedAggregateRoot
{
    /**
     * @var Account
     */
    private $beneficiaryParty;

    public function getBeneficiaryParty(): Account
    {
        return $this->beneficiaryParty;
    }

    /**
     * @var Account
     */
    private $debtorParty;

    public function getDebtorParty(): Account
    {
        return $this->debtorParty;
    }

    /**
     * @var Money
     */
    private $amount;

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
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
    private $removed = false;

    public function isRemoved(): bool
    {
        return $this->removed;
    }

    public function getTitle(): string
    {
        return sprintf('%s [%s]', $this->getDebtorParty()->getTitle(), $this->getAmount()->getValue());
    }

    public static function transfer(Account $beneficiaryParty, Account $debtorParty, Money $amount, DateTime $date): self
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(new TransferRegistered($id, $beneficiaryParty, $debtorParty, $amount, $date));

        return $self;
    }

    public function change(Account $beneficiaryParty, Account $debtorParty, Money $amount, DateTime $date)
    {
        if ($this->removed) {
            throw new TransferRemovedException('Change not possible, transfer removed.');
        }

        $this->recordChange(new TransferChanged($this->getId(), $beneficiaryParty, $debtorParty, $amount, $date));
    }

    public function remove()
    {
        if ($this->removed) {
            throw new TransferRemovedException('Remove not possible, transfer removed.');
        }

        $this->recordChange(new TransferRemoved($this->getId()));
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case TransferRegistered::class:
                /** @var TransferRegistered $event */
                $event = $changed->getPayload();

                $this->id = $changed->getAggregateId();
                $this->beneficiaryParty = $event->getBeneficiaryParty();
                $this->debtorParty = $event->getDebtorParty();
                $this->amount = $event->getAmount();
                $this->date = $event->getDate();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case TransferChanged::class:
                /** @var TransferChanged $event */
                $event = $changed->getPayload();

                $this->beneficiaryParty = $event->getBeneficiaryParty();
                $this->debtorParty = $event->getDebtorParty();
                $this->amount = $event->getAmount();
                $this->date = $event->getDate();
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case TransferRemoved::class:
                $this->removed = true;

                break;
        }
    }
}
