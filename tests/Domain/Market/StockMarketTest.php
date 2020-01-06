<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Currency;
use PHPUnit\Framework\TestCase;

class StockMarketTest extends TestCase
{

    public function testRegister()
    {
        $name = 'Stock Market';
        $currency = Currency::usd();
        $country = 'US';
        $symbol = 'NasdaqGS';

        $stockMarket = StockMarket::register($name, $currency, $country, $symbol);

        $this->assertEquals($name, $stockMarket->getName());
        $this->assertEquals($currency, $stockMarket->getCurrency());
        $this->assertEquals($country, $stockMarket->getCountry());
        $this->assertEquals($symbol, $stockMarket->getSymbol());
    }
}
