<?php

namespace App\Application\Market\Scraper;

use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;

class StockMarketCrawled
{
    /**
     * @var string
     */
    private $symbol;

    /**
     * @var Currency|null
     */
    private $currency;

    /**
     * @var Money|null
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
        string $symbol,
        ?Currency $currency = null
    ) {
        $this->symbol = $symbol;
        $this->currency = $currency;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function getValue(): ?Money
    {
        return $this->value;
    }

    public function setValue(?Money $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getChangePrice(): ?Money
    {
        return $this->changePrice;
    }

    public function setChangePrice(?Money $changePrice): self
    {
        $this->changePrice = $changePrice;

        return $this;
    }

    public function getPreClose(): ?Money
    {
        return $this->preClose;
    }

    public function setPreClose(?Money $preClose): self
    {
        $this->preClose = $preClose;

        return $this;
    }

    public function getOpen(): ?Money
    {
        return $this->open;
    }

    public function setOpen(?Money $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getDayLow(): ?Money
    {
        return $this->dayLow;
    }

    public function setDayLow(?Money $dayLow): self
    {
        $this->dayLow = $dayLow;

        return $this;
    }

    public function getDayHigh(): ?Money
    {
        return $this->dayHigh;
    }

    public function setDayHigh(?Money $dayHigh): self
    {
        $this->dayHigh = $dayHigh;

        return $this;
    }

    public function getWeek52Low(): ?Money
    {
        return $this->week52Low;
    }

    public function setWeek52Low(?Money $week52Low): self
    {
        $this->week52Low = $week52Low;

        return $this;
    }

    public function getWeek52High(): ?Money
    {
        return $this->week52High;
    }

    public function setWeek52High(?Money $week52High): self
    {
        $this->week52High = $week52High;

        return $this;
    }
}
