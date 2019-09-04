<?php

namespace App\Api;

use App\Entity;
use App\VO\Money;
use Symfony\Component\DependencyInjection\Tests\Fixtures\StdClassDecorator;

class Wallet
{
    public $id;

    public $name;

    public $funds;

    public $invested;

    public $capital;

    public $netCapital;

    public $margin;

    public $benefits;

    public $pBenefits;

    public $broker;

    public $title;

    public $dividend;

    public $commissions;

    public $connection;

    public $interest;

    public $dividendProjected;

    public $displayDividendProjectedYield;

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
        $self->margin = ($self->netCapital->multiply(0.475))->increase($wallet->getFunds());

        $self->benefits = $wallet->getBenefits();

        if ($self->invested->getCurrency()->equals($self->benefits->getCurrency())) {
            $self->pBenefits = $self->invested->getValue() ? $self->benefits->getValue() * 100 / $self->invested->getValue() : 0;
        }

        $self->dividend = $wallet->getDividend();
        $self->commissions = $wallet->getCommissions();
        $self->connection = $wallet->getConnection();
        $self->interest = $wallet->getInterest();

        $self->broker = Broker::fromEntity($wallet->getBroker());

        // dividend Projected
        $now = new \DateTimeImmutable();

        $year = $now->format('Y');
        $dividendProjectedMonths[$year] = [];

        if ($wallet->getMetadata() !== null) {
            $dividendYear = $wallet->getMetadata()->getDividendYear($year);
            if ($dividendYear !== null) {
                $self->dividendProjected = $dividendYear->getProjected()->increase($dividendYear->getPaid());

                foreach ($dividendYear->getMonths() as $month) {
                    $dividendProjectedMonth = $month->getProjected();

                    if ($dividendProjectedMonth !== null) {
                        $dividendProjectedMonth = $dividendProjectedMonth->increase($month->getPaid());
                    } else {
                        $dividendProjectedMonth = $month->getPaid();
                    }

                    $dividendProjectedMonths[$year][$month->getMonth()] = $dividendProjectedMonth;
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

        $self->displayDividendProjectedYield = sprintf(
            '%s (%.2f%%)',
            $self->dividendProjected,
            $self->dividendProjected->getValue() * 100 / $wallet->getInvested()->getValue()
        );

        $self->metadata = $wallet->getMetadata();

        $self->title = (string) $wallet;

        return $self;
    }
}

