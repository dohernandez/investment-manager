<?php

namespace App\Api;

use App\Entity;

class Position
{
    public $id;

    public $stock;

    public $amount;

    public $capital;

    public $invested;

    public $dividend;

    public $benefits;

    public $pBenefits;

    public $change;

    public $title;

    static public function fromEntity(Entity\Position $position): self
    {
        $self = new static();

        $self->id = $position->getId();
        $self->stock = Stock::fromEntity($position->getStock());
        $self->amount = $position->getAmount();
        $self->capital = $position->getCapital();
        $self->invested = $position->getInvested();
        $self->dividend = $position->getDividend();

        $self->benefits = $position->getBenefits();
        $self->pBenefits = $position->getPercentageBenefits();
        $self->change = $position->getChange();

        $self->title = (string) $position;

        return $self;
    }
}
