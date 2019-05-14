<?php

namespace App\Api;

use App\Entity;

class Stock
{
    public $id;

    public $name;

    public $symbol;

    public $value;

    public $title;

    static public function fromEntity(Entity\Stock $stock): self
    {
        $self = new static();

        $self->id = $stock->getId();
        $self->name = $stock->getName();
        $self->symbol = $stock->getSymbol();
        $self->value = $stock->getValue();

        $self->title = (string) $stock;

        return $self;
    }
}
