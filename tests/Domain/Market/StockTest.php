<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\Stock;
use App\Domain\Market\StockInfo;
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
        $sector = StockInfoProvider::provide('Stock Info', StockInfo::SECTOR);

        $stock = Stock::add($name, $symbol, $market, null, null, $sector);

        $this->assertInstanceOf(Stock::class, $stock);
        $this->assertNotNull($stock->getId());
        $this->assertEquals($name, $stock->getName());
        $this->assertEquals($symbol, $stock->getSymbol());
        $this->assertEquals($market, $stock->getMarket());
        $this->assertEquals($sector, $stock->getSector());
        $this->assertNull($stock->getDescription());
        $this->assertNull($stock->getType());
        $this->assertNull($stock->getIndustry());
    }
}
