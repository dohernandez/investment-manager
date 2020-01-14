<?php

namespace App\Tests\Application\Market\Handler;

use App\Application\Market\Command\AddStock;
use App\Application\Market\Handler\AddStockHandler;
use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockInfo;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Tests\Domain\Market\StockInfoProvider;
use App\Tests\Domain\Market\StockMarketProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AddStockHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $stockInfoType = StockInfoProvider::provide('Type', StockInfo::TYPE);
        $stockInfoSector = StockInfoProvider::provide('Sector', StockInfo::SECTOR);
        $stockInfoIndustry = StockInfoProvider::provide('Industry', StockInfo::INDUSTRY);

        $market = StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS');

        $stockInfoRepository = $this->prophesize(StockInfoRepositoryInterface::class);
        $stockInfoRepository->save($stockInfoType)->shouldBeCalled();
        $stockInfoRepository->save($stockInfoSector)->shouldBeCalled();
        $stockInfoRepository->save($stockInfoIndustry)->shouldBeCalled();

        $name = 'Stock';
        $symbol = 'STK';
        $value = Money::fromEURValue(1000);

        $stockRepository = $this->prophesize(StockRepositoryInterface::class);
        $stockRepository->save(
            Argument::that(
                function (Stock $stock) use ($name, $symbol, $value) {
                    $this->assertEquals($name, $stock->getName());
                    $this->assertEquals($symbol, $stock->getSymbol());
                    $this->assertEquals($symbol, $stock->getSymbol());
                    $this->assertEquals($value, $stock->getValue());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new AddStockHandler(
            $stockRepository->reveal(),
            $stockInfoRepository->reveal()
        );
        $handler(
            new AddStock(
                $name,
                $symbol,
                $symbol,
                $market,
                $value,
                null,
                $stockInfoType,
                $stockInfoSector,
                $stockInfoIndustry
            )
        );
    }
}
