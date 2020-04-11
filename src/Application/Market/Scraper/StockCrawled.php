<?php

namespace App\Application\Market\Scraper;

use App\Domain\Market\MarketData;
use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;

class StockCrawled
{
    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string|null
     */
    private $yahooSymbol;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var StockMarket|null
     */
    private $market;

    /**
     * @var Currency|null
     */
    private $currency;

    /**
     * @var Money|null
     */
    private $price;

    /**
     * @var Money|null
     */
    private $changePrice;

    /**
     * @var Money|null
     */
    private $preClose;

    /**
     * @var MarketData|null
     */
    private $data;

    /**
     * @var float|null
     */
    private $peRatio;

    /**
     * @var Money|null
     */
    private $week52Low;

    /**
     * @var Money|null
     */
    private $week52High;

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
        string $symbol,
        ?string $yahooSymbol = null,
        ?string $name = null,
        ?StockMarket $market = null,
        ?Currency $currency = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null
    ) {
        $this->symbol = $symbol;
        $this->yahooSymbol = $yahooSymbol;
        $this->name = $name;
        $this->market = $market;
        $this->currency = $currency;
        $this->type = $type;
        $this->sector = $sector;
        $this->industry = $industry;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMarket(): ?StockMarket
    {
        return $this->market;
    }

    public function setMarket(?StockMarket $market): self
    {
        $this->market = $market;

        if ($market !== null) {
            $this->setCurrency($market->getCurrency());
            if($data = $this->getData()) {
                $data->setCurrency($market->getCurrency());
            }
        }

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function setPrice(?Money $price): self
    {
        $this->price = $price;

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

    public function getData(): ?MarketData
    {
        return $this->data;
    }

    public function setData(?MarketData $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getPeRatio(): ?float
    {
        return $this->peRatio;
    }

    public function setPeRatio(?float $peRatio): self
    {
        $this->peRatio = $peRatio;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?StockInfo
    {
        return $this->type;
    }

    public function setType(?StockInfo $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSector(): ?StockInfo
    {
        return $this->sector;
    }

    public function setSector(?StockInfo $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getIndustry(): ?StockInfo
    {
        return $this->industry;
    }

    public function setIndustry(?StockInfo $industry): self
    {
        $this->industry = $industry;

        return $this;
    }
}
