<?php

namespace App\Api;

use App\Entity;

class Transfer
{
    public $id;

    public $beneficiaryParty;

    public $debtorParty;

    public $amount;

    public $date;

    public $title;

    static public function fromEntity(Entity\Transfer $transfer): self
    {
        $self = new static();

        $self->id = $transfer->getId();
        $self->beneficiaryParty = Account::fromEntity($transfer->getBeneficiaryParty());
        $self->debtorParty = Account::fromEntity($transfer->getDebtorParty());
        $self->amount = $transfer->getAmount();
        $self->date = $transfer->getDate();

        $self->title = (string) $transfer;

        return $self;
    }
}
