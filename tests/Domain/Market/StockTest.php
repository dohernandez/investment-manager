<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\Stock;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    public function testAdd()
    {
        $name = 'Stock';
        $symbol = 'STK';
        $market = StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS');
        $value = Money::fromEURValue(10);

        $stock = Stock::add($name, $symbol, $market, $value);

        $this->assertEquals($name, $stock->getName());
        $this->assertEquals($symbol, $stock->getSymbol());
        $this->assertEquals($market, $stock->getMarket());
        $this->assertEquals($value, $stock->getValue());
    }
}
