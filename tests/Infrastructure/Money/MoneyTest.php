<?php

namespace App\Tests\Infrastructure\Money;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group infrastructure
 * @group money
 */
final class MoneyTest extends TestCase
{
    /**
     * @dataProvider usdValueDataProvided
     */
    public function testFromValue(int $value, int $precision, float $preciseValue, string $currency, Money $money)
    {
        $divisionBy = (int)str_pad(1, $precision + 1, '0', STR_PAD_RIGHT);

        self::assertEquals($value, $money->getValue());
        self::assertEquals($precision, $money->getPrecision());
        self::assertEquals($divisionBy, $money->getDivisorBy());
        self::assertEquals($preciseValue, $money->getPreciseValue());
        self::assertEquals($currency, $money->getCurrency()->getCurrencyCode());
    }

    public function usdValueDataProvided()
    {
        return [
            [
                $value = 1500,
                $precision = 2,
                $preciseValue = 15,
                $currency = Currency::CURRENCY_CODE_USD,
                Money::fromUSDValue($value),

            ],
            [
                $value = 1500,
                $precision = 4,
                $preciseValue = 0.15,
                $currency = Currency::CURRENCY_CODE_EUR,
                Money::fromEURValue($value, $precision),
            ],
            [
                $value = 1500,
                $precision = 2,
                $preciseValue = 15,
                $currency = Currency::CURRENCY_CODE_CAD,
                Money::fromCADValue($value, $precision),
            ],
        ];
    }

    public function testDivide()
    {
        $money = Money::fromEURValue(1500)->divide(2);

        $this->assertEquals(750, $money->getValue());
        $this->assertEquals(Currency::CURRENCY_CODE_EUR, $money->getCurrency()->getCurrencyCode());
    }

    public function testExchange()
    {
        $exchangePaar =  Currency::eur()->getPaarExchangeRate(Currency::usd());
        $exchanges = [
            $exchangePaar => 1.100,
        ];

        $money = Money::fromUSDValue(5500)->exchange(Currency::eur(), $exchanges);

        $this->assertEquals(4999, $money->getValue());
    }

    public function testMultiply()
    {
        $money = Money::fromEURValue(1500)->multiply(2);

        $this->assertEquals(3000, $money->getValue());
        $this->assertEquals(Currency::CURRENCY_CODE_EUR, $money->getCurrency()->getCurrencyCode());
    }

    public function testDecrease()
    {
        $money = Money::fromEURValue(1500)->decrease(Money::fromEURValue(500));

        $this->assertEquals(1000, $money->getValue());
        $this->assertEquals(Currency::CURRENCY_CODE_EUR, $money->getCurrency()->getCurrencyCode());
    }

    public function testDecreaseFailDifferentCurrency()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('can not decrease money value, currency are different');

        Money::fromEURValue(1500)->decrease(Money::fromUSDValue(500));
    }

    public function testIncrease()
    {
        $money = Money::fromEURValue(1500)->increase(Money::fromEURValue(500));

        $this->assertEquals(2000, $money->getValue());
        $this->assertEquals(Currency::CURRENCY_CODE_EUR, $money->getCurrency()->getCurrencyCode());
    }

    public function testIncreaseFailDifferentCurrency()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('can not increase money value, currency are different');

        Money::fromEURValue(1500)->increase(Money::fromUSDValue(500));
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(string $toString, Money $money)
    {
        $this->assertEquals($toString, (string)$money);
    }

    public function toStringDataProvider()
    {
        return [
            [
                '$ 6.00',
                Money::fromUSDValue(600),
            ],
            [
                '$ -6.00',
                Money::fromUSDValue(-600),
            ],
            [
                '€ 43.03',
                Money::fromEURValue(4303),
            ],
            [
                '€ -43.03',
                Money::fromEURValue(-4303),
            ],
            [
                '16.00 C$',
                Money::fromCADValue(1600),
            ],
            [
                '-16.00 C$',
                Money::fromCADValue(-1600),
            ],
        ];
    }
}
