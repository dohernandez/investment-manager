<?php

namespace App\Application\Wallet\Decorator;

use App\Domain\Wallet\Wallet;

interface WalletOpenOrOpenedPositionYearWithDividendsDecorateInterface extends WalletPositionDecorateInterface
{
    public function setYear(int $year);
}
