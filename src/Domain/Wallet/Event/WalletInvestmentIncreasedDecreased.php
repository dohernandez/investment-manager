<?php

namespace App\Domain\Wallet\Event;

use App\Infrastructure\Money\Money;

abstract class WalletInvestmentIncreasedDecreased
{
    /**
     * @var Money
     */
    protected $funds;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var Money
     */
    protected $invested;

    public function __construct(string $id, Money $invested, Money $funds)
    {
        $this->id = $id;
        $this->funds = $funds;
        $this->invested = $invested;
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
}
