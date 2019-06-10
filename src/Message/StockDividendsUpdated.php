<?php

namespace App\Message;

use App\Entity\Stock;

final class StockDividendsUpdated
{
    /**
     * @var Stock
     */
    private $stock;

    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return Stock
     */
    public function getStock(): Stock
    {
        return $this->stock;
    }
}
