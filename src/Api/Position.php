<?php

namespace App\Api;

use App\Entity;

class Position
{
    public $id;

    public $stock;

    public $amount;

    public $capital;

    public $pCapital;

    public $displayCapital;

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

    public $dividendRetention;

    public $displayDividendToPay;

    static public function fromEntity(Entity\Position $position): self
    {
        $self = new static();

        $self->id = $position->getId();
        $self->stock = Stock::fromEntity($position->getStock());
        $self->amount = $position->getAmount();
        $self->capital = $position->getCapital();
        $self->pCapital = $position->getPercentageCapital();
        $self->displayCapital = sprintf(
            '%s (%.2f%%)',
            $self->capital,
            $self->pCapital
        );
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
            if ($self->metadata->getDividend()) {
                $self->displayDividendYield = sprintf(
                    '%s (%.2f%%)',
                    $self->metadata->getDividend()->multiply($self->amount),
                    $self->metadata->getDividendYield()
                );
            }

            if ($self->metadata->getDividendToPay()) {
                $self->displayDividendToPay = (string) $self->metadata->getDividendToPay()->multiply($self->amount);
            }
        }

        $self->title = (string) $position;

        $self->openedAt = $position->getOpenedAt();
        $self->dividendRetention = $position->getDividendRetention();

        return $self;
    }
}
