<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\StockPrice;
use App\Infrastructure\Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group domain
 * @group market
 */
final class StockPriceTest extends TestCase
{
    public function testEquals()
    {
        $price1 = StockPriceProvider::provide(
            Money::fromUSDValue(778),
            Money::fromUSDValue(-5),
            Money::fromUSDValue(783),
            Money::fromUSDValue(788),
            18.05,
            Money::fromUSDValue(764),
            Money::fromUSDValue(788),
            Money::fromUSDValue(564),
            Money::fromUSDValue(893),
            59
        );

        $price2 = StockPriceProvider::provide(
            Money::fromUSDValue(778),
            Money::fromUSDValue(-5),
            Money::fromUSDValue(783),
            Money::fromUSDValue(788),
            18.05,
            Money::fromUSDValue(764),
            Money::fromUSDValue(788),
            Money::fromUSDValue(564),
            Money::fromUSDValue(893),
            59
        );

        $this->assertTrue($price1->equals($price2));
    }
}
