<?php

namespace App\Domain\Market;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class StockMarketMetadata implements DataInterface
{
    use Data;
}
