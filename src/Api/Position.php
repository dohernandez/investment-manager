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

    public $displayBenefits;

    public $change;

    public $displayChange;

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
        $self->displayBenefits = sprintf(
            '%s (%.2f%%)',
            $self->benefits,
            $self->pBenefits
        );

        $self->change = $position->getChange();
        $self->displayChange = sprintf(
            '€ %.2f (%.2f%%)',
            $self->change,
            round($self->change * 100 / $position->getPreClose(), 2)
        );

        $self->title = (string) $position;

        return $self;
    }
}
