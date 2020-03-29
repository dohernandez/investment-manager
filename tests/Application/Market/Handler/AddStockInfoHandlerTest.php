<?php

namespace App\Tests\Application\Market\Handler;

use App\Application\Market\Command\AddStockInfo;
use App\Application\Market\Handler\AddStockInfoHandler;
use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Domain\Market\StockInfo;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AddStockInfoHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $name = 'Stock info type';
        $type = StockInfo::TYPE;

        $stockInfoRepository = $this->prophesize(StockInfoRepositoryInterface::class);
        $stockInfoRepository->save(
            Argument::that(
                function (StockInfo $stockInfo) use ($name, $type) {
                    $this->assertEquals($name, $stockInfo->getName());
                    $this->assertEquals($type, $stockInfo->getType());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new AddStockInfoHandler($stockInfoRepository->reveal());
        $handler(new AddStockInfo($name, $type));
    }
}
