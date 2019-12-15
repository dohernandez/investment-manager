<?php

namespace App\Domain\Transfer;

use App\Domain\Transfer\Event\TransferRegistered;
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

    public static function transfer(string $beneficiaryParty, string $debtorParty, Money $amount, DateTime $date): self
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(new TransferRegistered($id, $beneficiaryParty, $debtorParty, $amount, $date));

        return $self;
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
        }
    }
}
