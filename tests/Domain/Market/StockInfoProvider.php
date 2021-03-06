<?php

namespace App\Tests\Domain\Market;

use App\Domain\Market\StockInfo;

final class StockInfoProvider
{
    public static function provide(
        string $name,
        string $type
    ): StockInfo {
        return StockInfo::add($name, $type);
    }
}
