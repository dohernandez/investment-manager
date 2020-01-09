<?php

namespace App\Tests\Application\Market\Handler;

use App\Application\Market\Command\AddStock;
use App\Application\Market\Command\AddStockInfo;
use App\Application\Market\Handler\AddStockHandler;
use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockInfo;
use App\Infrastructure\Money\Currency;
use App\Tests\Domain\Market\StockInfoProvider;
use App\Tests\Domain\Market\StockMarketProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class AddStockHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $stockInfoTypeName = 'Type';
        $stockInfoSectorName = 'Sector';
        $stockInfoIndustryName = 'Industry';

        $stockInfoType = StockInfoProvider::provide($stockInfoTypeName, StockInfo::TYPE);
        $stockInfoSector = StockInfoProvider::provide($stockInfoSectorName, StockInfo::SECTOR);
        $stockInfoIndustry = StockInfoProvider::provide($stockInfoIndustryName, StockInfo::INDUSTRY);

        $marketId = 'marketId';
        $market = StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS');

        $envelop = (new Envelope($stockInfoSector, HandledStamp::fromCallable(function () {}, $stockInfoSector)));
        $bus = $this->prophesize(MessageBusInterface::class);
        $bus->dispatch(new AddStockInfo($stockInfoSectorName, StockInfo::SECTOR))->shouldBeCalled()->willReturn(
            $envelop
        );

        $projectionStockMarketRepository = $this->prophesize(ProjectionStockMarketRepositoryInterface::class);
        $projectionStockMarketRepository->find($marketId)->shouldBeCalled()->willReturn($market);

        $projectionStockInfoRepository = $this->prophesize(ProjectionStockInfoRepositoryInterface::class);
        $projectionStockInfoRepository->findByName($stockInfoTypeName)->shouldBeCalled()->willReturn($stockInfoType);
        $projectionStockInfoRepository->findByName($stockInfoIndustryName)->shouldBeCalled()->willReturn(
            $stockInfoIndustry
        );

        $name = 'Stock';
        $symbol = 'STK';
        $value = 1000;

        $stockRepository = $this->prophesize(StockRepositoryInterface::class);
        $stockRepository->save(
            Argument::that(
                function (Stock $stock) use ($name, $symbol, $value) {
                    $this->assertEquals($name, $stock->getName());
                    $this->assertEquals($symbol, $stock->getSymbol());
                    $this->assertEquals($value, $stock->getValue()->getValue());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new AddStockHandler(
            $bus->reveal(),
            $stockRepository->reveal(),
            $projectionStockMarketRepository->reveal(),
            $projectionStockInfoRepository->reveal()
        );
        $handler(
            new AddStock(
                $name,
                $symbol,
                $marketId,
                $value,
                null,
                $stockInfoTypeName,
                $stockInfoSectorName,
                $stockInfoIndustryName
            )
        );
    }
}
