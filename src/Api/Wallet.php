<?php

namespace App\Api;

use App\Entity;

class Wallet
{
    public $id;

    public $name;

    public $funds;

    public $invested;

    public $capital;

    public $benefits;

    public $pBenefits;

    public $broker;

    public $title;

    static public function fromEntity(Entity\Wallet $wallet): self
    {
        $self = new static();

        $self->id = $wallet->getId();
        $self->name = $wallet->getName();
        $self->funds = $wallet->getFunds();
        $self->invested = $wallet->getInvested();
        $self->capital = $wallet->getCapital();

        $self->benefits = $wallet->getBenefits();
        $self->pBenefits = $self->benefits * 100 / $self->invested;


        $self->broker = Broker::fromEntity($wallet->getBroker());

        $self->title = (string) $wallet;

        return $self;
    }
}
