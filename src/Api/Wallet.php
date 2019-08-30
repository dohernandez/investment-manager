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

    public $dividendProjected;

    public $dividendProjectedIncrease;

    public $dividendProjectedMonths;

    public $comingDividends;

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

        $now = new \DateTimeImmutable();

        $year = $now->format('Y');
        $dividendProjectedMonths[$year] = [];

        if ($wallet->getMetadata() !== null) {
            $dividendYear = $wallet->getMetadata()->getDividendYear($year);
            if ($dividendYear !== null) {
                $self->dividendProjected = $dividendYear->getProjected()->increase($dividendYear->getPaid());

                foreach ($dividendYear->getMonths() as $month) {
                    $dividendProjectedMonths[$year][$month->getMonth()] = $month->getProjected();
                }
            }

            $previousYear = $year - 1;
            $dividendProjectedMonths[$previousYear] = [];

            $dividendPreviousYear = $wallet->getMetadata()->getDividendYear($previousYear);
            if ($dividendPreviousYear !== null) {
                $self->dividendProjectedIncrease = ($self->dividendProjected->getValue() - $dividendPreviousYear->getPaid()->getValue())
                    / $self->dividendProjected->getValue() * 100;

                foreach ($dividendPreviousYear->getMonths() as $month) {
                    $dividendProjectedMonths[$previousYear][$month->getMonth()] = $month->getPaid();
                }
            }
            $self->dividendProjectedMonths = $dividendProjectedMonths;
        }

        $self->comingDividends = [];

        $self->metadata = $wallet->getMetadata();

        $self->title = (string) $wallet;

        return $self;
    }
}

