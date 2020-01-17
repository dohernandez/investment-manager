<?php

namespace App\Domain\Market;

use App\Application\Market\Command\StockPriceUpdated;
use App\Domain\Market\Event\StockAdded;
use App\Domain\Market\Event\StockPriceLinked;
use App\Domain\Market\Event\StockUpdated;
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
     * @var StockPrice
     */
    private $price;

    public function getPrice(): ?StockPrice
    {
        return $this->price;
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

    public function getCurrency(): Currency
    {
        return $this->getMarket()->getCurrency();
    }

    public function getTitle(): string
    {
        return sprintf(
            '%s (%s:%s)',
            $this->getName(),
            $this->getSymbol(),
            $this->getMarket()->getSymbol()
        );
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

    public function update(
        ?string $name = null,
        ?string $yahooSymbol = null,
        ?StockMarket $market = null,
        ?Money $value = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null
    ): self {
        $this->recordChange(
            new StockUpdated(
                $this->getId(),
                $name,
                $yahooSymbol,
                $market,
                $value,
                $description,
                $type,
                $sector,
                $industry
            )
        );

        return $this;
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
                $this->metadata = new StockMetadata();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case StockUpdated::class:
                /** @var StockUpdated $event */
                $event = $changed->getPayload();

                $this->name = $event->getName();
                $this->value = $event->getValue();
                $this->description = $event->getDescription();
                $this->metadata = $this->metadata->updateYahooSymbol($event->getYahooSymbol());
                $this->updatedAt = $changed->getCreatedAt();
                $this->market = $event->getMarket();
                $this->type = $event->getType();
                $this->sector = $event->getSector();
                $this->industry = $event->getIndustry();

                break;

            case StockPriceLinked::class:
                /** @var StockPriceLinked $event */
                $event = $changed->getPayload();

                $this->price = $event->getPrice();
                break;
        }
    }

    public function linkPrice(): self
    {
        $this->recordChange(
            new StockPriceLinked(
                $this->getId(),
                $this->price
            )
        );

        return $this;
    }

    public function updatePrice(StockPrice $price): self
    {
        if (!$this->price) {
            $this->price = $price;

            return $this;
        }

        $this->price->setPrice($price->getPrice());
        $this->price->setChangePrice($price->getChangePrice());
        $this->price->setPeRatio($price->getPeRatio());
        $this->price->setPreClose($price->getPreClose());
        $this->price->setOpen($price->getOpen());
        $this->price->setDayLow($price->getDayLow());
        $this->price->setDayHigh($price->getDayHigh());
        $this->price->setWeek52Low($price->getWeek52Low());
        $this->price->setWeek52High($price->getWeek52High());

        return $this;
    }
}
