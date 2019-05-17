<?php

namespace App\Message;

use App\Entity\StockDividend;

class StockDividendSaved
{
    /** @var StockDividend */
    private $stockDividend;

    public function __construct(StockDividend $stockDividend)
    {
        $this->stockDividend = $stockDividend;
    }

    /**
     * @return StockDividend
     */
    public function getStockDividend(): StockDividend
    {
        return $this->stockDividend;
    }
}
