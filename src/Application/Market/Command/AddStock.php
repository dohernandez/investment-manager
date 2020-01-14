<?php

namespace App\Application\Market\Command;

use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Money;

final class AddStock
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
     * @var string
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

    public function __construct(
        string $name,
        string $symbol,
        string $yahooSymbol,
        StockMarket $market,
        ?Money $value = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null
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
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getYahooSymbol(): string
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
}
