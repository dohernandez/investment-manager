<?php

namespace App\Domain\Market;

use App\Domain\Market\Event\StockDividendAdded;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use InvalidArgumentException;

use function sprintf;

class StockDividend
{
    public const STATUS_PROJECTED = 'projected';
    public const STATUS_ANNOUNCED = 'announced';
    public const STATUS_PAYED = 'payed';

    public const STATUS = [
        self::STATUS_PROJECTED,
        self::STATUS_ANNOUNCED,
        self::STATUS_PAYED
    ];

    public const FREQUENCY_MONTHlY = '1 months';
    public const FREQUENCY_QUARTERLY = '3 months';
    public const FREQUENCY_YEARLY = '1 year';

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public static function getFrequencyMultiplier(string $frequency): int
    {
        $frequencyMultiplier = null;
        switch ($frequency) {
            case self::FREQUENCY_MONTHlY:
                $frequencyMultiplier = 12;

                break;
            case self::FREQUENCY_QUARTERLY:
                $frequencyMultiplier = 4;

                break;
            case self::FREQUENCY_YEARLY:
                $frequencyMultiplier = 1;

                break;
            default:
                throw new InvalidArgumentException(sprintf('Frequency not supported [%s]', $frequency));
        }

        return $frequencyMultiplier;
    }

    /**
     * @var int|null
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @var DateTime|null
     */
    private $exDate;

    public function getExDate(): ?DateTime
    {
        return $this->exDate;
    }

    public function setExDate(?DateTime $exDate): self
    {
        $this->exDate = $exDate;

        return $this;
    }

    /**
     * @var DateTime|null
     */
    private $paymentDate;

    public function getPaymentDate(): ?DateTime
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?DateTime $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * @var DateTime|null
     */
    private $recordDate;

    public function getRecordDate(): ?DateTime
    {
        return $this->recordDate;
    }

    public function setRecordDate(?DateTime $recordDate): self
    {
        $this->recordDate = $recordDate;

        return $this;
    }

    /**
     * @var string|null
     */
    private $status;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @var Money|null
     */
    private $value;

    public function getValue(): ?Money
    {
        return $this->value;
    }

    public function setValue(?Money $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @var float|null
     */
    private $changeFromPrev;

    public function getChangeFromPrev(): ?float
    {
        return $this->changeFromPrev;
    }

    public function setChangeFromPrev(?float $changeFromPrev): self
    {
        $this->changeFromPrev = $changeFromPrev;

        return $this;
    }

    /**
     * @var float|null
     */
    private $changeFromPrevYear;

    public function getChangeFromPrevYear(): ?float
    {
        return $this->changeFromPrevYear;
    }

    public function setChangeFromPrevYear(?float $changeFromPrevYear): self
    {
        $this->changeFromPrevYear = $changeFromPrevYear;

        return $this;
    }

    /**
     * @var float|null
     */
    private $prior12MonthsYield;

    public function getPrior12MonthsYield(): ?float
    {
        return $this->prior12MonthsYield;
    }

    public function setPrior12MonthsYield(?float $prior12MonthsYield): self
    {
        $this->prior12MonthsYield = $prior12MonthsYield;

        return $this;
    }

    /**
     * @var Stock|null
     */
    private $stock;

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @var DateTime|null
     */
    private $createdAt;

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->getValue() . '(' . $this->getStatus() . ')';
    }

    public function isBefore(StockDividend $dividend)
    {
        return $dividend->getExDate() > $this->getExDate() ||
        (
            $dividend->getExDate() === $this->getExDate() &&
            (
                $dividend->getRecordDate() > $this->getRecordDate() ||
                $dividend->getPaymentDate() > $this->getPaymentDate()
            )
        );
    }
}
