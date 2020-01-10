<?php

namespace App\Domain\Market;

use App\Domain\Market\Event\StockAdded;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Stock extends AggregateRoot implements EventSourcedAggregateRoot
{
    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->dividends = new ArrayCollection();
    }

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
     * @var StockMarket
     */
    private $market;

    public function getMarket(): StockMarket
    {
        return $this->market;
    }

    /**
     * @var Money
     */
    private $value;

    public function getValue(): ?Money
    {
        return $this->value;
    }

    /**
     * @var string
     */
    private $description;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @var StockInfo
     */
    private $type;

    public function getType(): ?StockInfo
    {
        return $this->type;
    }

    /**
     * @var StockInfo
     */
    private $sector;

    public function getSector(): ?StockInfo
    {
        return $this->sector;
    }

    /**
     * @var StockInfo
     */
    private $industry;

    public function getIndustry(): ?StockInfo
    {
        return $this->industry;
    }

    /**
     * @var ArrayCollection|StockDividend[]
     */
    private $dividends;

    public function getDividends(): ArrayCollection
    {
        return $this->dividends;
    }

    /**
     * @var string
     */
    private $notes;

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @var StockMetadata
     */
    private $metadata;

    public function getMetadata(): ?StockMetadata
    {
        return $this->metadata;
    }

    /**
     * @var StockDividend
     */
    private $nextDividend;

    public function getNextDividend(): ?StockDividend
    {
        return $this->nextDividend;
    }

    /**
     * @var StockDividend
     */
    private $toPayDividend;

    public function getToPayDividend(): ?StockDividend
    {
        return $this->toPayDividend;
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
     * @var DateTime
     */
    private $updatedAt;

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @var DateTime
     */
    private $updatedPriceAt;

    public function getUpdatedPriceAt(): ?DateTime
    {
        return $this->updatedPriceAt;
    }

    /**
     * @var DateTime
     */
    private $updatedDividendAt;

    public function getUpdatedDividendAt(): ?DateTime
    {
        return $this->updatedDividendAt;
    }

    public function getCurrency(): Currency
    {
        return $this->getMarket()->getCurrency();
    }

    public static function add(
        string $name,
        string $symbol,
        StockMarket $market,
        ?Money $value = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null
    ): self {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(
            new StockAdded(
                $id,
                $name,
                $symbol,
                $market,
                $value,
                $description,
                $type,
                $sector,
                $industry
            )
        );

        return $self;
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case StockAdded::class:
                /** @var StockAdded $event */
                $event = $changed->getPayload();

                $this->id = $changed->getAggregateId();
                $this->name = $event->getName();
                $this->symbol = $event->getSymbol();
                $this->market = $event->getMarket();
                $this->value = $event->getValue();
                $this->description = $event->getDescription();
                $this->type = $event->getType();
                $this->sector = $event->getSector();
                $this->industry = $event->getIndustry();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;
        }
    }
}
