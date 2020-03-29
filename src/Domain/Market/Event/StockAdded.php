<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockDividend;
use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Money;

final class StockAdded
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var StockMarket
     */
    private $market;

    /**
     * @var string|null
     */
    private $yahooSymbol;

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
     * @var string|null
     */
    private $dividendFrequency;

    public function __construct(
        string $id,
        string $name,
        string $symbol,
        StockMarket $market,
        ?string $yahooSymbol = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null,
        ?string $dividendFrequency = StockDividend::FREQUENCY_QUARTERLY
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->symbol = $symbol;
        $this->market = $market;
        $this->yahooSymbol = $yahooSymbol;
        $this->description = $description;
        $this->type = $type;
        $this->sector = $sector;
        $this->industry = $industry;
        $this->dividendFrequency = $dividendFrequency;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getMarket(): StockMarket
    {
        return $this->market;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
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

    public function getDividendFrequency(): ?string
    {
        return $this->dividendFrequency;
    }
}
