<?php

namespace App\Application\Market\Event;

use App\Domain\Market\StockPrice;

final class StockPriceUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var StockPrice
     */
    private $price;

    public function __construct(string $id, StockPrice $price)
    {
        $this->id = $id;
        $this->price = $price;
    }

    public function getPrice(): StockPrice
    {
        return $this->price;
    }
}
