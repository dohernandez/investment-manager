<?php

namespace App\Domain\Market;

use App\Domain\Market\Event\StockDividendAdded;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;

class StockDividend extends AggregateRoot implements EventSourcedAggregateRoot
{
    public const STATUS_PROJECTED = 'projected';
    public const STATUS_ANNOUNCED = 'announced';
    public const STATUS_PAYED = 'payed';

    public const STATUS = [self::STATUS_PROJECTED, self::STATUS_ANNOUNCED, self::STATUS_PAYED];

    /**
     * @var DateTime
     */
    private $exDate;

    public function getExDate(): DateTime
    {
        return $this->exDate;
    }

    /**
     * @var DateTime
     */
    private $paymentDate;

    public function getPaymentDate(): DateTime
    {
        return $this->paymentDate;
    }

    /**
     * @var DateTime
     */
    private $recordDate;

    public function getRecordDate(): DateTime
    {
        return $this->recordDate;
    }

    /**
     * @var string
     */
    private $status;

    public function getStatus(): string
    {
        return $this->status;
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
     * @var float
     */
    private $changeFromPrev;

    public function getChangeFromPrev(): float
    {
        return $this->changeFromPrev;
    }

    /**
     * @var float
     */
    private $changeFromPrevYear;

    public function getChangeFromPrevYear(): float
    {
        return $this->changeFromPrevYear;
    }

    /**
     * @var float
     */
    private $prior12MonthsYield;

    public function getPrior12MonthsYield(): float
    {
        return $this->prior12MonthsYield;
    }

    /**
     * @var Stock
     */
    private $stock;

    public function getStock(): Stock
    {
        return $this->stock;
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

    public static function add(
        Stock $stock,
        Money $value,
        DateTime $exDate,
        string $status = self::STATUS_PROJECTED,
        ?DateTime $paymentDate = null,
        ?DateTime $recordDate = null
    ) {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $self->recordChange(new StockDividendAdded($id, $stock, $value, $exDate, $status, $paymentDate, $recordDate));

        return $self;
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case StockDividendAdded::class:
                /** @var StockDividendAdded $event */
                $event = $changed->getPayload();

                $this->id = $changed->getAggregateId();
                $this->stock = $event->getStock();
                $this->value = $event->getValue();
                $this->exDate = $event->getExDate();
                $this->status = $event->getStatus();
                $this->paymentDate = $event->getPaymentDate();
                $this->recordDate = $event->getRecordDate();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;
        }
    }
}
