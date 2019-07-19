<?php

namespace App\Api;

use App\Entity;
use App\VO\Money;

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

    public $metadata;

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

        if ($self->invested->getCurrency()->equals($self->benefits->getCurrency())) {
            $self->pBenefits = $self->invested->getValue() ? $self->benefits->getValue() * 100 / $self->invested->getValue() : 0;
        }

        $self->dividend = $wallet->getDividend();
        $self->commissions = $wallet->getCommissions();
        $self->connection = $wallet->getConnection();
        $self->interest = $wallet->getInterest();


        $self->broker = Broker::fromEntity($wallet->getBroker());

        $self->metadata = $wallet->getMetadata();

        $self->title = (string) $wallet;

        return $self;
    }
}
