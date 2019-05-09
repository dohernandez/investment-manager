<?php

namespace App\Api;

use App\Entity;

class StockMarket
{
    public $id;

    public $name;

    public $country;

    public $countryName;

    public $symbol;

    public $title;

    static public function fromEntity(Entity\StockMarket $stockMarket): self
    {
        $self = new static();

        $self->id = $stockMarket->getId();
        $self->name = $stockMarket->getName();
        $self->country = $stockMarket->getCountry();
        $self->countryName = $stockMarket->getCountryName();
        $self->symbol = $stockMarket->getSymbol();

        $self->title = (string) $stockMarket;

        return $self;
    }
}
