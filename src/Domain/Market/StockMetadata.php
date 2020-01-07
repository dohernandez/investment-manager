<?php

namespace App\Domain\Market;

use App\Infrastructure\Money\Money;

final class StockMetadata
{
    /**
     * @var StockPrice
     */
    private $price;

    /**
     * @var float
     */
    private $dividendYield;

    public function __construct(
        StockPrice $price,
        float $dividendYield
    ) {
        $this->price = $price;
        $this->dividendYield = $dividendYield;
    }

    /**
     * @return StockPrice
     */
    public function getPrice(): StockPrice
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getDividendYield(): float
    {
        return $this->dividendYield;
    }

    public function updateDividendYield(float $dividendYield): self
    {
        return new static($this->price, $dividendYield);
    }

    public function updatePrice(StockPrice $price): self
    {
        return new static($price, $this->dividendYield);
    }
}
