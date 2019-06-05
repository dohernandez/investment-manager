<?php

namespace App\Api;

use App\Entity;

class Operation
{
    public $id;

    public $stock;

    public $amount;

    public $capital;

    public $invested;

    public $type;

    public $title;

    static public function fromEntity(Entity\Operation $operation): self
    {
        $self = new static();

        $self->id = $operation->getId();
        $self->stock = Stock::fromEntity($operation->getStock());
        $self->amount = $operation->getAmount();
        $self->capital = $operation->getCapital();
        $self->invested = $operation->getNetValue();
        $self->type = $operation->getType();

        $self->title = (string) $operation;

        return $self;
    }
}
