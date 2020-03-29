<?php

namespace App\Tests\Domain\Wallet;

use App\Domain\Wallet\Market;
use App\Domain\Wallet\Position;
use App\Domain\Wallet\PositionBook;
use App\Domain\Wallet\Rate;
use App\Domain\Wallet\Stock;
use App\Domain\Wallet\StockDividend;
use App\Domain\Wallet\Wallet;
use App\Domain\Wallet\WalletBook;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class WalletTest extends TestCase
{

    /**
     * @dataProvider CalculateYearDividendProjectedDataProvider
     *
     * @param int $year
     * @param array $positions
     * @param Rate[] $exchangeMoneyRate
     * @param array $dividendProjectionResult
     */
    public function testCalculateYearDividendProjected(
        int $year,
        array $positions,
        array $exchangeMoneyRate,
        array $dividendProjectionResult
    ) {
        $wallet = new Wallet(UUID\Generator::generate());
        $reflect = new ReflectionClass(get_class($wallet));
        $property = $reflect->getProperty('book');
        $property->setAccessible(true);
        $property->setValue($wallet, WalletBook::createWithInitialBalance(Currency::eur()));

        $positions = $this->positionsProvider($positions);
        $wallet->setPositions($positions);

        $wallet->calculateYearDividendProjected($year, $exchangeMoneyRate);

        $bookDividendProjection = $wallet->getBook()->getDividendsProjection();
        $bookYear = $bookDividendProjection->getBookEntry($year);
        $this->assertEquals($dividendProjectionResult['total'], $bookYear->getTotal()->getPreciseValue());
        foreach ($dividendProjectionResult['year'] as $key => $result) {
            $this->assertEquals($result, $bookYear->getBookEntry($key)->getTotal()->getPreciseValue());
        }
    }

    private function positionsProvider(array $positionsData): ArrayCollection
    {
        $positions = new ArrayCollection();

        foreach ($positionsData as $positionData) {
            $positions->add($this->positionProvider($positionData));
        }

        return $positions;
    }

    private function positionProvider(array $positionData): Position
    {
        $position = $this->prophesize(Position::class);

        $position->getAmount()->shouldBeCalled()->willReturn($positionData['amount']);
        $position->getBook()->shouldBeCalled()->willReturn(
            $this->positionBookProvider($positionData['dividend_retention'])
        );
        $position->getStock()->shouldBeCalled()->willReturn($this->stockProvider($positionData['stock']));

        return $position->reveal();
    }

    private function positionBookProvider(?Money $retention): PositionBook
    {
        $positionBook = $this->prophesize(PositionBook::class);

        $positionBook->getTotalDividendRetention()->shouldBeCalled()->willReturn($retention);

        return $positionBook->reveal();
    }

    private function stockProvider(array $stockData): Stock
    {
        $stock = new Stock(
            UUID\Generator::generate(),
            $stockData['name'],
            $stockData['symbol'],
            new Market(
                UUID\Generator::generate(),
                $stockData['market_name'],
                $stockData['market_symbol'],
                $stockData['currency']
            )
        );

        $dividends = new ArrayCollection();

        foreach ($stockData['dividends'] as $dividendData) {
            $dividends->add(
                new StockDividend(
                    new DateTime($dividendData['date']),
                    new Money(
                        $stockData['currency'],
                        $dividendData['value'] * 100
                    )
                )
            );
        }

        $stock = $stock->appendStockDividends($dividends);

        return $stock;
    }

    public function CalculateYearDividendProjectedDataProvider()
    {
        $exchangeMoneyRates = [
            new Rate(Currency::usd(), Currency::eur(), '1'),
        ];

        return [
            'calculate with one position without dividend retention' => [
                2020,
                [
                    $positionWDC = [
                        'amount'             => 4,
                        'dividend_retention' => null,
                        'stock'              => [
                            'name'          => 'WESTERN DIGITAL CORPOR',
                            'symbol'        => 'WDC',
                            'currency'      => Currency::usd(),
                            'market_name'   => 'NASDAQ COMPOSITE',
                            'market_symbol' => 'NASDAQ',
                            'dividends'     => [
                                [
                                    'date'  => '2019-10-03',
                                    'value' => 0.50,
                                ],
                                [
                                    'date'  => '2020-01-02',
                                    'value' => 0.50,
                                ],
                                [
                                    'date'  => '2020-04-02',
                                    'value' => 0.50,
                                ],
                                [
                                    'date'  => '2020-07-02',
                                    'value' => 0.50,
                                ],
                                [
                                    'date'  => '2020-10-02',
                                    'value' => 0.50,
                                ],
                                [
                                    'date'  => '2021-01-02',
                                    'value' => 0.50,
                                ],
                            ],
                        ],
                    ],
                ],
                $exchangeMoneyRates,
                [
                    'total' => 8,
                    'year'  => [
                        '1'  => 2,
                        '4'  => 2,
                        '7'  => 2,
                        '10' => 2,
                    ],
                ]
            ],

            'calculate with two position without dividend retention, but one position one time' => [
                2020,
                [
                    $positionWDC,
                    $positionVET = [
                        'amount'             => 2,
                        'dividend_retention' => null,
                        'stock'              => [
                            'name'          => 'Vermilion Energy Inc',
                            'symbol'        => 'VET',
                            'currency'      => Currency::usd(),
                            'market_name'   => $marketName = 'New York Stock Exchange',
                            'market_symbol' => $marketSymbol = 'NYSE',
                            'dividends'     => [
                                [
                                    'date'  => '2020-01-30',
                                    'value' => 0.08,
                                ],
                                [
                                    'date'  => '2020-02-27',
                                    'value' => 0.08,
                                ],
                            ],
                        ],
                    ],
                ],
                $exchangeMoneyRates,
                [
                    'total' => 8.32,
                    'year'  => [
                        '1'  => 2.16,
                        '2'  => 0.16,
                        '4'  => 2,
                        '7'  => 2,
                        '10' => 2,
                    ],
                ],
            ],
        ];
    }
}
