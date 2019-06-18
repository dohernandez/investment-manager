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

    public $netCapital;

    public $benefits;

    public $pBenefits;

    public $broker;

    public $title;

    public $dividend;

    public $commissions;

    public $connection;

    public $interest;

    static public function fromEntity(Entity\Wallet $wallet): self
    {
        $self = new static();

        $self->id = $wallet->getId();
        $self->name = $wallet->getName();
        $self->funds = $wallet->getFunds();
        $self->invested = $wallet->getInvested();
        $self->capital = $wallet->getCapital();
        $self->netCapital = $wallet->getNetCapital();

        $self->benefits = $wallet->getBenefits();
        $self->pBenefits = $self->invested ? $self->benefits * 100 / $self->invested : 0;

        $self->dividend = $wallet->getDividend();
        $self->commissions = $wallet->getCommissions();
        $self->connection = $wallet->getConnection();
        $self->interest = $wallet->getInterest();


        $self->broker = Broker::fromEntity($wallet->getBroker());

        $self->title = (string) $wallet;

        return $self;
    }
}
