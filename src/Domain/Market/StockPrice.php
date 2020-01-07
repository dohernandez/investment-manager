<?php

namespace App\Domain\Market;

use App\Infrastructure\Money\Money;

final class StockPrice
{
    /**
     * @var Money
     */
    private $price;

    /**
     * @var Money
     */
    private $changePrice;

    /**
     * @var float
     */
    private $peRatio;

    /**
     * @var Money
     */
    private $preClose;

    /**
     * @var Money
     */
    private $open;

    /**
     * @var Money
     */
    private $dayLow;

    /**
     * @var Money
     */
    private $dayHigh;

    /**
     * @var Money
     */
    private $week52Low;

    /**
     * @var Money
     */
    private $week52High;

    public function __construct(
        Money $price,
        Money $preClose,
        Money $open,
        float $peRatio,
        Money $dayLow,
        Money $dayHigh,
        Money $week52Low,
        Money $week52High
    ) {
        $this->price = $price;
        $this->preClose = $preClose;
        $this->open = $open;
        $this->peRatio = $peRatio;
        $this->dayLow = $dayLow;
        $this->dayHigh = $dayHigh;
        $this->week52Low = $week52Low;
        $this->week52High = $week52High;

        $this->changePrice = $price->decrease($open)->decrease($preClose);
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getDividendYield(): float
    {
        return $this->dividendYield;
    }

    public function getChangePrice(): Money
    {
        return $this->changePrice;
    }

    public function getPeRatio(): float
    {
        return $this->peRatio;
    }

    public function getPreClose(): Money
    {
        return $this->preClose;
    }

    public function getOpen(): Money
    {
        return $this->open;
    }

    public function getDayLow(): Money
    {
        return $this->dayLow;
    }

    public function getDayHigh(): Money
    {
        return $this->dayHigh;
    }

    public function getWeek52Low(): Money
    {
        return $this->week52Low;
    }

    public function getWeek52High(): Money
    {
        return $this->week52High;
    }
}
