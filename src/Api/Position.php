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

    public $displayDividendYield;

    public $metadata;

    public $title;

    public $openedAt;

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
        if ($self->change !== null && $position->getPreClose() !== null) {
            $self->displayChange = sprintf(
                '%s (%.2f%%)',
                $self->change,
                $self->change->getValue() * 100 / $position->getPreClose()->getValue()
            );
        }

        $self->metadata = $position->getMetadata();

        if ($self->metadata !== null) {
            $self->displayDividendYield = sprintf(
                '%s (%.2f%%)',
                $self->metadata->getDividend()->multiply($self->amount),
                $self->metadata->getDividendYield()
            );
        }

        $self->title = (string) $position;

        $self->openedAt = $position->getOpenedAt();

        return $self;
    }
}
