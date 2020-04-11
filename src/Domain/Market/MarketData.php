<?php

namespace App\Domain\Market;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;

class MarketData
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var Money|null
     */
    private $open;

    /**
     * @var Money|null
     */
    private $close;

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
    private $weekLow;

    /**
     * @var Money|null
     */
    private $weekHigh;

    /**
     * @var DateTime|null
     */
    private $dateAt;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var StockMarket
     */
    private $market;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpen(): Money
    {
        return $this->open;
    }

    public function setOpen(?Money $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getClose(): ?Money
    {
        return $this->close;
    }

    public function setClose(?Money $close): self
    {
        $this->close = $close;

        return $this;
    }

    public function getDayLow(): Money
    {
        return $this->dayLow;
    }

    public function setDayLow(?Money $dayLow): self
    {
        $this->dayLow = $dayLow;

        return $this;
    }

    public function getDayHigh(): Money
    {
        return $this->dayHigh;
    }

    public function setDayHigh(?Money $dayHigh): self
    {
        $this->dayHigh = $dayHigh;

        return $this;
    }

    public function getWeekLow(): ?Money
    {
        return $this->weekLow;
    }

    public function setWeekLow(?Money $weekLow): self
    {
        $this->weekLow = $weekLow;

        return $this;
    }

    public function getWeekHigh(): ?Money
    {
        return $this->weekHigh;
    }

    public function setWeekHigh(?Money $weekHigh): self
    {
        $this->weekHigh = $weekHigh;

        return $this;
    }

    public function getDateAt(): ?DateTime
    {
        return $this->dateAt;
    }

    public function setDateAt(?DateTime $dateAt): self
    {
        $this->dateAt = $dateAt;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function equals(self $marketData): bool
    {
        if (
            $this->dateAt != $marketData->getDateAt() ||
            !$this->getCurrency()->equals($marketData->getCurrency()) ||
            !$this->open->equals($marketData->getOpen()) ||
            !$this->dayLow->equals($marketData->getDayLow()) ||
            !$this->dayHigh->equals($marketData->getDayHigh())
        ){
            return false;
        }

        if (
            ($this->close === null && $marketData->getClose() !== null) ||
            ($this->close !== null && $marketData->getClose() === null) ||
            ($this->weekLow === null && $marketData->getWeekLow() !== null) ||
            ($this->weekLow !== null && $marketData->getWeekLow() === null) ||
            ($this->weekHigh === null && $marketData->getWeekHigh() !== null) ||
            ($this->weekHigh !== null && $marketData->getWeekHigh() === null)
        ) {
            return false;
        }

        return (
            // if they are equals it is because they both are null
            ($this->close === $marketData->getClose() || $this->close->equals($marketData->getClose())) &&
            ($this->weekLow === $marketData->getWeekLow() || $this->weekLow->equals($marketData->getWeekLow())) &&
            ($this->weekHigh === $marketData->getWeekHigh() || $this->weekHigh->equals($marketData->getWeekHigh()))
        );
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function setStock(Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getMarket(): StockMarket
    {
        return $this->market;
    }

    public function setMarket(StockMarket $market): self
    {
        $this->market = $market;

        return $this;
    }
}
