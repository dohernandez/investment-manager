<?php

namespace App\Application\Market\Decorator;

use App\Domain\Market\Stock;

interface StockDividendsDecorateInterface
{
    public function decorate(Stock $stock);
}
