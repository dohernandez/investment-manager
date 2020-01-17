<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockPrice;

final class StockPriceLinked
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var StockPrice
     */
    private $price;

    public function __construct(
        string $id,
        StockPrice $price
    ) {
        $this->id = $id;
        $this->price = $price;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPrice(): StockPrice
    {
        return $this->price;
    }

}
