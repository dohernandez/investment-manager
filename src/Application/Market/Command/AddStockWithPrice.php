<?php

namespace App\Application\Market\Command;

use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Money;

final class AddStockWithPrice
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string|null
     */
    private $yahooSymbol;

    /**
     * @var StockMarket
     */
    private $market;

    /**
     * @var Money|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var StockInfo|null
     */
    private $type;

    /**
     * @var StockInfo|null
     */
    private $sector;

    /**
     * @var StockInfo|null
     */
    private $industry;

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
        string $name,
        string $symbol,
        ?string $yahooSymbol,
        StockMarket $market,
        ?Money $value = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null,
        ?Money $preClose = null,
        ?Money $open = null,
        ?float $peRatio = null,
        ?Money $dayLow = null,
        ?Money $dayHigh = null,
        ?Money $week52Low = null,
        ?Money $week52High = null
    ) {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->yahooSymbol = $yahooSymbol;
        $this->market = $market;
        $this->value = $value;
        $this->description = $description;
        $this->type = $type;
        $this->sector = $sector;
        $this->industry = $industry;
        $this->preClose = $preClose;
        $this->open = $open;
        $this->peRatio = $peRatio;
        $this->dayLow = $dayLow;
        $this->dayHigh = $dayHigh;
        $this->week52Low = $week52Low;
        $this->week52High = $week52High;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }

    public function getMarket(): StockMarket
    {
        return $this->market;
    }

    public function getValue(): ?Money
    {
        return $this->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function getType(): ?StockInfo
    {
        return $this->type;
    }


    public function getSector(): ?StockInfo
    {
        return $this->sector;
    }


    public function getIndustry(): ?StockInfo
    {
        return $this->industry;
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
