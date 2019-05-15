<?php

namespace App\Api;

use App\Entity;

class StockInfo
{
    public $id;

    public $name;

    public $type;

    public $title;

    static public function fromEntity(Entity\StockInfo $stockInfo): self
    {
        $self = new static();

        $self->id = $stockInfo->getId();
        $self->name = $stockInfo->getName();
        $self->type = $stockInfo->getType();

        $self->title = (string) $stockInfo;

        return $self;
    }
}
