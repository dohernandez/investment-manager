<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\StockInfo;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group domain
 * @group market
 */
final class StockInfoTest extends TestCase
{

    public function testAdd()
    {
        $name = 'Stock info type';
        $type = StockInfo::TYPE;

        $stockInfo = StockInfo::add($name, $type);

        $this->assertInstanceOf(StockInfo::class, $stockInfo);
        $this->assertNotNull($stockInfo->getId());
        $this->assertEquals($name, $stockInfo->getName());
        $this->assertEquals($type, $stockInfo->getType());
    }
}
