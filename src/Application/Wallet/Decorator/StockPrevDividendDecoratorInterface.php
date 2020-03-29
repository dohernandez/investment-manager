<?php

namespace App\Application\Wallet\Decorator;

use App\Domain\Wallet\Stock;
use DateTime;

interface StockPrevDividendDecoratorInterface
{
    public function decorate(Stock &$stock, DateTime $date);
}
