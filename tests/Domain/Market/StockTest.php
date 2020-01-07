<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\Stock;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group domain
 * @group market
 */
final class StockTest extends TestCase
{
    public function testAdd()
    {
        $name = 'Stock';
        $symbol = 'STK';
        $market = StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS');
        $value = Money::fromEURValue(1000);

        $stock = Stock::add($name, $symbol, $market, $value);

        $this->assertInstanceOf(Stock::class, $stock);
        $this->assertNotNull($stock->getId());
        $this->assertEquals($name, $stock->getName());
        $this->assertEquals($symbol, $stock->getSymbol());
        $this->assertEquals($market, $stock->getMarket());
        $this->assertEquals($value, $stock->getValue());
    }
}
