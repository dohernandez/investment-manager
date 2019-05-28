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

    public $title;

    static public function fromEntity(Entity\Wallet $wallet): self
    {
        $self = new static();

        $self->id = $wallet->getId();
        $self->name = $wallet->getName();
        $self->funds = $wallet->getFunds();
        $self->invested = $wallet->getInvested();
        $self->capital = $wallet->getCapital();

        $self->title = (string) $wallet;

        return $self;
    }
}
