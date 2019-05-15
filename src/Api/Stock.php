<?php

namespace App\Api;

use App\Entity;
use App\Api;

class Stock
{
    public $id;

    public $name;

    public $symbol;

    public $value;

    public $market;

    public $description;

    public $type;

    public $sector;

    public $industry;

    public $title;

    static public function fromEntity(Entity\Stock $stock): self
    {
        $self = new static();

        $self->id = $stock->getId();
        $self->name = $stock->getName();
        $self->symbol = $stock->getSymbol();
        $self->value = $stock->getValue();
        $self->description = $stock->getDescription();

        $self->market = Api\StockMarket::fromEntity($stock->getMarket());

        $self->type = $stock->getType() ? Api\StockInfo::fromEntity($stock->getType()) : null;
        $self->sector = $stock->getSector() ? Api\StockInfo::fromEntity($stock->getSector()) : null;
        $self->industry = $stock->getIndustry() ? Api\StockInfo::fromEntity($stock->getIndustry()) : null;

        $self->title = (string) $stock;

        return $self;
    }
}
