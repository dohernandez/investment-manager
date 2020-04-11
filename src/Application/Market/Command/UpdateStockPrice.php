<?php

namespace App\Application\Market\Command;

use App\Domain\Market\MarketData;
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

    public function __construct(
        string $id,
        Money $price,
        ?Money $changePrice = null,
        ?Money $preClose = null,
        ?MarketData $data = null,
        ?float $peRatio = null,
        ?Money $week52Low = null,
        ?Money $week52High = null
    ) {
        $this->id = $id;
        $this->price = $price;
        $this->changePrice = $changePrice;
        $this->preClose = $preClose;
        $this->data = $data;
        $this->peRatio = $peRatio;
        $this->week52Low = $week52Low;
        $this->week52High = $week52High;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getChangePrice(): ?Money
    {
        return $this->changePrice;
    }

    public function getPreClose(): ?Money
    {
        return $this->preClose;
    }

    public function getData(): ?MarketData
    {
        return $this->data;
    }

    public function getPeRatio(): ?float
    {
        return $this->peRatio;
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
