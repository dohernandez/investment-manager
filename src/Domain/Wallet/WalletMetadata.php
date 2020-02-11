<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Money;

final class WalletMetadata
{
    /**
     * @var Money|null
     */
    private $invested;

    /**
     * @var Money|null
     */
    private $capital;

    /**
     * @var Money|null
     */
    private $funds;

    /**
     * @var BookMetadata|null
     */
    private $dividend;

    /**
     * @var BookMetadata|null
     */
    private $commissions;

    /**
     * @var BookMetadata|null
     */
    private $connection;

    /**
     * @var BookMetadata|null
     */
    private $interest;

    /**
     * @var Money|null
     */
    private $benefits;

    public function getInvested(): ?Money
    {
        return $this->invested;
    }

    public function getCapital(): ?Money
    {
        return $this->capital;
    }

    public function getFunds(): ?Money
    {
        return $this->funds;
    }

    public function getDividend(): ?BookMetadata
    {
        return $this->dividend;
    }

    public function getCommissions(): ?BookMetadata
    {
        return $this->commissions;
    }

    public function getConnection(): ?BookMetadata
    {
        return $this->connection;
    }

    public function getInterest(): ?BookMetadata
    {
        return $this->interest;
    }

    public function getBenefits(): ?Money
    {
        return $this->benefits;
    }
}
