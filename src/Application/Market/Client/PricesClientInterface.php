<?php

namespace App\Application\Market\Client;

use App\Infrastructure\Context\Context;
use App\Infrastructure\Money\Currency;
use DateTimeInterface;

interface PricesClientInterface
{
    public function getHistoricalData(Context $context, Currency $currency, string $stock, DateTimeInterface $date): array;
}
