<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use App\Domain\Market\StockInfo;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;
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

    /**
     * @dataProvider syncDividendsDataProvider
     *
     * @param Stock $stock
     * @param array $dividends
     * @param StockDividend|null $nextDividend
     * @param StockDividend|null $toPayDividend
     * @param int $countDividends
     *
     * @throws \Exception
     */
    public function testSyncDividends(
        Stock $stock,
        array $dividends,
        ?StockDividend $nextDividend,
        ?StockDividend $toPayDividend,
        int $countDividends
    ) {
        $stock->syncDividends($dividends, '2020-01-19');

        $this->assertEquals($nextDividend, $stock->getNextDividend(), 'next dividend is not equals');
        $this->assertEquals($toPayDividend, $stock->getToPayDividend(), 'to pay dividend is not equals');
        $this->assertCount($countDividends, $stock->getDividends());
    }

    public function syncDividendsDataProvider()
    {
        $stock = $stock = StockProvider::provide(
            'Stock',
            'STK',
            StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS')
        );

        return [
            'with new next projected' => [
                $stock,
                [
                    StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(32),
                        new DateTime('2017-04-05'),
                        StockDividend::STATUS_PAYED,
                        new DateTime('2017-05-10'),
                        new DateTime('2017-04-07')
                    ),
                    $nextDividend = StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(43),
                        new DateTime('2020-01-20'),
                        StockDividend::STATUS_PROJECTED
                    ),
                ],
                $nextDividend,
                null,
                2
            ],

            'with next projected sync and new to pay announcement' => [
                $stock,
                [
                    StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(32),
                        new DateTime('2017-04-05'),
                        StockDividend::STATUS_PAYED,
                        new DateTime('2017-05-10'),
                        new DateTime('2017-04-07')
                    ),
                    $toPayDividend = StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(43),
                        new DateTime('2020-01-02'),
                        StockDividend::STATUS_ANNOUNCED,
                        new DateTime('2020-02-10'),
                        new DateTime('2020-01-03')
                    ),
                    $nextDividend = StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(43),
                        new DateTime('2020-04-02'),
                        StockDividend::STATUS_PROJECTED
                    ),
                ],
                $nextDividend,
                $toPayDividend,
                3
            ],

            'with next projected sync and to pay announcement sync, but removed' => [
                $stock,
                [
                    StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(43),
                        new DateTime('2020-01-02'),
                        StockDividend::STATUS_PAYED,
                        new DateTime('2020-02-18'),
                        new DateTime('2020-01-03')
                    ),
                    $nextDividend = StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(43),
                        new DateTime('2020-04-02'),
                        StockDividend::STATUS_PROJECTED
                    ),
                    StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(43),
                        new DateTime('2020-07-02'),
                        StockDividend::STATUS_PROJECTED
                    ),
                ],
                $nextDividend,
                null,
                4
            ],

            'with no next projected sync, but next projected removed' => [
                $stock,
                [
                    StockDividendProvider::provide(
                        $stock,
                        Money::fromUSDValue(43),
                        new DateTime('2020-01-02'),
                        StockDividend::STATUS_PAYED,
                        new DateTime('2020-02-18'),
                        new DateTime('2020-01-03')
                    ),
                ],
                null,
                null,
                2
            ],
        ];
    }

}
