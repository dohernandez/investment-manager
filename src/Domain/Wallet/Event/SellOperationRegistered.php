<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\ExchangeRate;
use App\Domain\Wallet\Stock;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Money\Money;
use DateTime;

final class SellOperationRegistered extends BuySellOperationRegistered
{
}
