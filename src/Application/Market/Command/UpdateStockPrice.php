<?php

namespace App\Application\Market\Command;

use App\Infrastructure\Money\Money;

final class UpdateStockPrice
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Money
     */
    private $value;

    /**
     * @var Money|null
     */
    private $changePrice;

    /**
     * @var Money|null
     */
    private $preClose;

    /**
     * @var Money|null
     */
    private $open;

    /**
     * @var float|null
     */
    private $peRatio;

    /**
     * @var Money|null
     */
    private $dayLow;

    /**
     * @var Money|null
     */
    private $dayHigh;

    /**
     * @var Money|null
     */
    private $week52Low;

    /**
     * @var Money|null
     */
    private $week52High;

    public function __construct(
        string $id,
        Money $value,
        ?Money $changePrice = null,
        ?Money $preClose = null,
        ?Money $open = null,
        ?float $peRatio = null,
        ?Money $dayLow = null,
        ?Money $dayHigh = null,
        ?Money $week52Low = null,
        ?Money $week52High = null
    ) {
        $this->id = $id;
        $this->value = $value;
        $this->changePrice = $changePrice;
        $this->preClose = $preClose;
        $this->open = $open;
        $this->peRatio = $peRatio;
        $this->dayLow = $dayLow;
        $this->dayHigh = $dayHigh;
        $this->week52Low = $week52Low;
        $this->week52High = $week52High;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getValue(): Money
    {
        return $this->value;
    }

    public function getChangePrice(): ?Money
    {
        return $this->changePrice;
    }

    public function getPreClose(): ?Money
    {
        return $this->preClose;
    }

    public function getOpen(): ?Money
    {
        return $this->open;
    }

    public function getPeRatio(): ?float
    {
        return $this->peRatio;
    }

    public function getDayLow(): ?Money
    {
        return $this->dayLow;
    }

    public function getDayHigh(): ?Money
    {
        return $this->dayHigh;
    }

    public function getWeek52Low(): ?Money
    {
        return $this->week52Low;
    }

    public function getWeek52High(): ?Money
    {
        return $this->week52High;
    }
}
