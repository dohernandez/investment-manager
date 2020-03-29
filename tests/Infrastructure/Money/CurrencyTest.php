<?php

namespace App\Tests\Infrastructure\Money;

use App\Infrastructure\Money\Currency;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group infrastructure
 * @group money
 */
final class CurrencyTest extends TestCase
{
    /**
     * @dataProvider fromCodeDataProvider
     */
    public function testFromCode(Currency $currency, string $symbol, string $code)
    {
        $this->assertEquals($symbol, $currency->getSymbol());
        $this->assertEquals($code, $currency->getCurrencyCode());
    }

    public function fromCodeDataProvider()
    {
        return [
            [
                Currency::usd(),
                Currency::CURRENCY_SYMBOL_USD,
                Currency::CURRENCY_CODE_USD,
            ],
            [
                Currency::eur(),
                Currency::CURRENCY_SYMBOL_EUR,
                Currency::CURRENCY_CODE_EUR,
            ],
            [
                Currency::cad(),
                Currency::CURRENCY_SYMBOL_CAD,
                Currency::CURRENCY_CODE_CAD,
            ],
        ];
    }

    public function testEquals()
    {
        $this->assertTrue(Currency::eur()->equals(Currency::eur()));
    }

    public function testNoEquals()
    {
        $this->assertFalse(Currency::eur()->equals(Currency::usd()));
    }

    public function testGetPaarExchangeRate()
    {
        $this->assertEquals('EUR_USD', Currency::eur()->getPaarExchangeRate(Currency::usd()));
    }
}
