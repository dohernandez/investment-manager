<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group domain
 * @group market
 */
final class StockDividendTest extends TestCase
{
    public function testAdd()
    {
        $stock = Stock::add(
            'Stock',
            'STK',
            StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS'),
            Money::fromEURValue(1000)
        );
        $value = Money::fromEURValue(1);
        $exDate = new DateTime('07-12-2020');

        $stockDividend = StockDividend::add($stock, $value, $exDate);

        $this->assertInstanceOf(StockDividend::class, $stockDividend);
        $this->assertNotNull($stockDividend->getId());
        $this->assertEquals($stock, $stockDividend->getStock());
        $this->assertEquals($value, $stockDividend->getValue());
        $this->assertEquals($exDate, $stockDividend->getExDate());
    }
}
