<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\WalletBook;
use App\Infrastructure\Money\Money;

class WalletInvestmentIncreased
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Money
     */
    private $funds;

    /**
     * @var Money
     */
    private $invested;

    /**
     * @var Money
     */
    private $capital;

    public function __construct(string $id, Money $invested, Money $capital, Money $funds)
    {
        $this->id = $id;
        $this->funds = $funds;
        $this->invested = $invested;
        $this->capital = $capital;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }
}
