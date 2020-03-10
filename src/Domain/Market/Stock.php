<?php

namespace App\Domain\Market;

use App\Domain\Market\Event\StockAdded;
use App\Domain\Market\Event\StockDelisted;
use App\Domain\Market\Event\StockDividendSynched;
use App\Domain\Market\Event\StockPriceUpdated;
use App\Domain\Market\Event\StockUpdated;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

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

    public function setDividends(ArrayCollection $dividends): self
    {
        $this->dividends = $dividends;

        return $this;
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

    public function getNextYearDividend(): ?Money
    {
        return $this->calculateYearDividend($this->nextDividend);
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
     * @var bool
     */
    private $delisted = false;

    public function isDelisted(): bool
    {
        return $this->delisted;
    }

    /**
     * @var DateTime|null
     */
    private $delistedAt;

    public function getDelistedAt(): ?DateTime
    {
        return $this->delistedAt;
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
    private $dividendsSyncAt;

    public function getDividendsSyncAt(): DateTime
    {
        return $this->dividendsSyncAt;
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
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null,
        ?string $dividendFrequency = null
    ): self {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(
            new StockAdded(
                $id,
                $name,
                $symbol,
                $market,
                null,
                $description,
                $type,
                $sector,
                $industry,
                $dividendFrequency
            )
        );

        return $self;
    }

    public function update(
        ?string $name = null,
        ?string $yahooSymbol = null,
        ?StockMarket $market = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null,
        ?string $dividendFrequency = null
    ): self {
        $this->recordChange(
            new StockUpdated(
                $this->getId(),
                $name,
                $yahooSymbol,
                $market,
                $description,
                $type,
                $sector,
                $industry,
                $dividendFrequency
            )
        );

        return $this;
    }

    public function updatePrice(StockPrice $price, $toUpdateAt = 'now'): self
    {
        $toUpdateAt = new DateTime($toUpdateAt);

        $dividendYield = $this->calculateNewDividendYield($price, $this->nextDividend);

        if ($changed = $this->findIfLastChangeHappenedIsName(StockPriceUpdated::class)) {
            // This is to avoid have too much update events.
            $this->price->setPrice($price->getPrice());
            $this->price->setChangePrice($price->getChangePrice());
            $this->price->setPeRatio($price->getPeRatio());
            $this->price->setPreClose($price->getPreClose());
            $this->price->setOpen($price->getOpen());
            $this->price->setDayLow($price->getDayLow());
            $this->price->setDayHigh($price->getDayHigh());
            $this->price->setWeek52Low($price->getWeek52Low());
            $this->price->setWeek52High($price->getWeek52High());

            $this->replaceChangedPayload(
                $changed,
                new StockPriceUpdated(
                    $this->getId(),
                    $this->price,
                    $toUpdateAt,
                    $dividendYield
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new StockPriceUpdated(
                $this->getId(),
                $price,
                $toUpdateAt,
                $dividendYield
            )
        );

        return $this;
    }

    private function calculateNewDividendYield(?StockPrice $price, ?StockDividend $dividend): ?float
    {
        $yearDividend = $this->calculateYearDividend($dividend);

        if (!$price || !$yearDividend) {
            return null;
        }

        return $yearDividend->getValue() / $price->getPrice()->getValue() * 100;
    }

    private function calculateYearDividend(?StockDividend $dividend): ?Money
    {
        if (!$dividend) {
            return null;
        }

        try {
            $frequencyMultiplier = StockDividend::getFrequencyMultiplier(
                $this->getMetadata()->getDividendFrequency()
            );

            return $dividend->getValue()->multiply($frequencyMultiplier);
        } catch (InvalidArgumentException $e) {
        }

        return null;
    }

    /**
     * @param StockDividend[] $dividends
     * @param string $toSyncAt
     *
     * @return $this
     * @throws \Exception
     */
    public function syncDividends(array $dividends, $toSyncAt = 'now'): self
    {
        $toSyncAt = new DateTime($toSyncAt);

        // remove announce and projected dividends
        $toRemove = [];

        foreach ($this->dividends as $dividend) {
            if ($dividend->getStatus() === StockDividend::STATUS_PROJECTED ||
                $dividend->getStatus() === StockDividend::STATUS_ANNOUNCED) {
                $toRemove[] = $dividend;
            }
        }

        // add new dividends and sync next and to pay dividend
        foreach ($toRemove as $dividend) {
            if ($this->dividends->contains($dividend)) {
                $this->dividends->removeElement($dividend);

                // set the owning side to null (unless already changed)
                if ($dividend->getStock() === $this) {
                    $dividend->setStock(null);
                }
            }
        }

        // sync next and to pay dividend
        $nextDividend = $this->nextDividend;
        $toPayDividend = $this->toPayDividend;

        foreach ($dividends as $k => $dividend) {
            if ($this->dividends->exists(
                function ($index, StockDividend $item) use ($dividend, $k) {
                    return $item->getStatus() === $dividend->getStatus() &&
                        $item->getExDate() == $dividend->getExDate() &&
                        $item->getPaymentDate() == $dividend->getPaymentDate() &&
                        $item->getRecordDate() == $dividend->getRecordDate() &&
                        $item->getValue()->equals($dividend->getValue());
                }
            )) {
                continue;
            }

            $this->dividends->add($dividend);
            $dividend->setStock($this);

            if (
                $dividend->getStatus() !== StockDividend::STATUS_PAYED &&
                (
                    $dividend->getExDate() > $toSyncAt &&
                    (
                        !$nextDividend ||
                        !$nextDividend->getStock() ||
                        (

                            $nextDividend->getExDate() > $dividend->getExDate() ||
                            (
                                $nextDividend->getExDate() === $dividend->getExDate() &&
                                (
                                    $nextDividend->getRecordDate() > $dividend->getRecordDate() ||
                                    $nextDividend->getPaymentDate() > $dividend->getPaymentDate()
                                )
                            )
                        )
                    )
                )
            ) {
                $nextDividend = $dividend;
            }

            if (
                $dividend->getStatus() === StockDividend::STATUS_ANNOUNCED &&
                $dividend->getExDate() < $toSyncAt &&
                $dividend->getPaymentDate() > $toSyncAt &&
                (
                    !$toPayDividend ||
                    !$toPayDividend->getStock()
                )
            ) {
                $toPayDividend = $dividend;
            }
        }

        if ($nextDividend && !$nextDividend->getStock()) {
            $nextDividend = null;
        }

        if ($toPayDividend && !$toPayDividend->getStock()) {
            $toPayDividend = null;
        }

        if ($nextDividend != $this->nextDividend || $toPayDividend != $this->toPayDividend) {
            $dividendYield = $this->calculateNewDividendYield($this->getPrice(), $nextDividend);

            $changed = $this->findIfLastChangeHappenedIsName(StockDividendSynched::class);

            if ($changed) {
                $this->replaceChangedPayload(
                    $changed,
                    new StockDividendSynched(
                        $this->getId(),
                        $nextDividend,
                        $toPayDividend,
                        $toSyncAt,
                        $dividendYield
                    ),
                    clone $toSyncAt
                );

                return $this;
            }

            $this->recordChange(
                new StockDividendSynched(
                    $this->getId(),
                    $nextDividend,
                    $toPayDividend,
                    $toSyncAt,
                    $dividendYield
                )
            );

            return $this;
        }

        return $this;
    }

    public function delisted($delistedAt = 'now'): self
    {
        $this->recordChange(
            new StockDelisted(
                $this->getId(),
                new DateTime($delistedAt)
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
                $this->description = $event->getDescription();
                $this->type = $event->getType();
                $this->sector = $event->getSector();
                $this->industry = $event->getIndustry();
                $this->metadata = (new StockMetadata())
                    ->changeYahooSymbol($event->getYahooSymbol())
                    ->changeDividendFrequency($event->getDividendFrequency());
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case StockUpdated::class:
                /** @var StockUpdated $event */
                $event = $changed->getPayload();

                $this->name = $event->getName();
                $this->description = $event->getDescription();
                $this->metadata = $this->metadata
                    ->changeYahooSymbol($event->getYahooSymbol())
                    ->changeDividendFrequency($event->getDividendFrequency());
                $this->updatedAt = $changed->getCreatedAt();
                $this->market = $event->getMarket();
                $this->type = $event->getType();
                $this->sector = $event->getSector();
                $this->industry = $event->getIndustry();

                $this->updatedAt = $changed->getCreatedAt();

                break;

            case StockPriceUpdated::class:
                /** @var StockPriceUpdated $event */
                $event = $changed->getPayload();

                $this->price = $event->getPrice();
                $this->metadata = $this->metadata->changeDividendYield($event->getDividendYield());

                $this->updatedAt = $changed->getCreatedAt();
                break;

            case StockDividendSynched::class:
                /** @var StockDividendSynched $event */
                $event = $changed->getPayload();

                $this->nextDividend = $event->getNextDividend();
                $this->toPayDividend = $event->getToPayDividend();
                $this->metadata = $this->metadata->changeDividendYield($event->getDividendYield());
                $this->dividendsSyncAt = $event->getSynchedAt();

                $this->updatedAt = $changed->getCreatedAt();
                break;

            case StockDelisted::class:
                /** @var StockDelisted $event */
                $event = $changed->getPayload();

                $this->delisted = true;
                $this->delistedAt = $event->getDelistedAt();

                break;
        }
    }
}
