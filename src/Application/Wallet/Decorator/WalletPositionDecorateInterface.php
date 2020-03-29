<?php

namespace App\Application\Wallet\Decorator;

use App\Domain\Wallet\Wallet;

interface WalletPositionDecorateInterface
{
    public function decorate(Wallet $wallet);
}
