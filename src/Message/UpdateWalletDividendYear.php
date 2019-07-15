<?php

namespace App\Message;

use App\Entity\Exchange;
use App\Entity\Wallet;

final class UpdateWalletDividendYear
{
    /**
     * @var Wallet
     */
    private $wallet;

    /**
     * @var array
     */
    private $exchangeRates;

    public function __construct(Wallet $wallet, array $exchangeRates)
    {
        $this->wallet = $wallet;
        $this->exchangeRates = $exchangeRates;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    /**
     * @return Exchange[]
     */
    public function getExchangeRates(): array
    {
        return $this->exchangeRates;
    }
}
