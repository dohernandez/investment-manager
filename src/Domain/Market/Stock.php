<?php

namespace App\Domain\Market;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;

class Stock extends AggregateRoot implements EventSourcedAggregateRoot
{
    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $symbol;

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @var Money
     */
    private $value;

    public function getValue(): Money
    {
        return $this->value;
    }

    /**
     * @var StockMarket
     */
    private $market;

    public function getMarket(): StockMarket
    {
        return $this->market;
    }

    /**
     * @var string
     */
    private $description;

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @var StockInfo
     */
    private $type;

    public function getType(): StockInfo
    {
        return $this->type;
    }

    /**
     * @var StockInfo
     */
    private $sector;

    public function getSector(): StockInfo
    {
        return $this->sector;
    }

    /**
     * @var StockInfo
     */
    private $industry;

    public function getIndustry(): StockInfo
    {
        return $this->industry;
    }

    /**
     * @var StockDividend
     */
    private $dividends;

    public function getDividends(): StockDividend
    {
        return $this->dividends;
    }

    /**
     * @var string
     */
    private $notes;

    public function getDotes(): string
    {
        return $this->notes;
    }

    /**
     * @var StockMetadata
     */
    private $metadata;

    public function getMetadata(): StockMetadata
    {
        return $this->metadata;
    }

    /**
     * @var DateTime
     */
    private $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @var StockDividend
     */
    private $nextDividend;

    public function getNextDividend(): StockDividend
    {
        return $this->nextDividend;
    }

    /**
     * @var StockDividend
     */
    private $toPayDividend;

    public function getToPayDividend(): StockDividend
    {
        return $this->toPayDividend;
    }

    /**
     * @var DateTime
     */
    private $updatedAt;

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getCurrency(): Currency
    {
        return $this->getMarket()->getCurrency();
    }

    /**
     * @var DateTime
     */
    private $updatedPriceAt;

    public function getUpdatedPriceAt(): DateTime
    {
        return $this->updatedPriceAt;
    }

    protected function apply(Changed $changed)
    {
        // TODO: Implement apply() method.
    }
}
