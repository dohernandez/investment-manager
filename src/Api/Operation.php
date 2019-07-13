<?php

namespace App\Api;

use App\Entity;

class Operation
{
    public $id;

    public $stock;

    public $amount;

    public $capital;

    public $value;

    public $type;

    public $dateAt;

    public $commissions;

    public $title;

    static public function fromEntity(Entity\Operation $operation): self
    {
        $self = new static();

        $self->id = $operation->getId();
        $self->stock = $operation->getStock() ? Stock::fromEntity($operation->getStock()) : null;
        $self->amount = $operation->getAmount();
        $self->value = $operation->getNetValue();
        $self->type = $operation->getType();
        $self->dateAt = $operation->getDateAt();
        $self->commissions = $operation->getFinalCommissionPaid();

        $self->title = (string) $operation;

        return $self;
    }
}
