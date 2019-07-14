<?php

namespace App\Message;

use App\Entity\Wallet;

final class OperationImportedInWallet
{
    /**
     * @var Wallet
     */
    private $wallet;

    public function __construct(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }

    /**
     * @return Wallet
     */
    public function getWallet(): Wallet
    {
        return $this->wallet;
    }
}
