<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Money\Money;
use DateTime;

final class WalletSellOperationUpdated extends WalletBuySellOperationUpdated
{
}
