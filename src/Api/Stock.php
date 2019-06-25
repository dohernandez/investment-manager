<?php

namespace App\Api;

use App\Entity;
use App\Api;

class Stock
{
    public $id;

    public $name;

    public $symbol;

    public $value;

    public $market;

    public $description;

    public $dividendYield;

    public $displayDividendYield;

    public $exDate;

    public $peRatio;

    public $preClose;

    public $open;

    public $dayLow;

    public $dayHigh;

    public $week52Low;

    public $week52High;

    public $change;

    public $changePercentage;

    public $type;

    public $sector;

    public $industry;

    public $title;

    static public function fromEntity(Entity\Stock $stock): self
    {
        $self = new static();

        $self->id = $stock->getId();
        $self->name = $stock->getName();
        $self->symbol = $stock->getSymbol();
        $self->value = $stock->getValue();
        $self->description = $stock->getDescription();
        $self->dividendYield = $stock->getDividendYield();

        $nextDividend = $stock->nextDividend();
        if ($nextDividend) {
            $self->exDate = $nextDividend->getExDate();
            $self->displayDividendYield = sprintf(
                '%.2f (%.2f%%)',
                $nextDividend->getValue() * 4,
                $self->dividendYield
            );
        }

        $self->market = $stock->getMarket() ? Api\StockMarket::fromEntity($stock->getMarket()) : null;

        $self->type = $stock->getType() ? Api\StockInfo::fromEntity($stock->getType()) : null;
        $self->sector = $stock->getSector() ? Api\StockInfo::fromEntity($stock->getSector()) : null;
        $self->industry = $stock->getIndustry() ? Api\StockInfo::fromEntity($stock->getIndustry()) : null;

        $self->peRatio = $stock->getPeRatio();
        $self->preClose = $stock->getPreClose();
        $self->open = $stock->getOpen();
        $self->dayLow = $stock->getDayLow();
        $self->dayHigh = $stock->getDayHigh();
        $self->week52Low = $stock->getWeek52Low();
        $self->week52High = $stock->getWeek52High();

        $self->change = round($stock->getChange(), 3);
        $self->changePercentage = $self->preClose
            ? round($self->change * 100 / $self->preClose, 3)
            : 0;

        $self->title = (string) $stock;

        return $self;
    }
}
