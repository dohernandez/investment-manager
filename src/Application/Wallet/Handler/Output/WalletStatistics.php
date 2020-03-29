<?php

namespace App\Application\Wallet\Handler\Output;

use App\Infrastructure\Money\Money;

final class WalletStatistics
{
    /**
     * @var Money
     */
    private $invested;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $netCapital;

    /**
     * @var Money
     */
    private $funds;

    /**
     * @var Money|null
     */
    private $dividends;

    /**
     * @var Money|null
     */
    private $commissions;

    /**
     * @var Money|null
     */
    private $connection;

    /**
     * @var Money|null
     */
    private $interest;

    /**
     * @var Money|null
     */
    private $benefits;

    /**
     * @var float|null
     */
    private $percentageBenefits;

    public function __construct(
        Money $invested,
        Money $capital,
        Money $netCapital,
        Money $funds,
        ?Money $dividends,
        ?Money $commissions,
        ?Money $connection,
        ?Money $interest,
        ?Money $benefits,
        ?float $percentageBenefits
    )
    {
        $this->invested = $invested;
        $this->capital = $capital;
        $this->netCapital = $netCapital;
        $this->funds = $funds;
        $this->dividends = $dividends;
        $this->commissions = $commissions;
        $this->connection = $connection;
        $this->interest = $interest;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
    }

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getNetCapital(): Money
    {
        return $this->netCapital;
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function getDividends(): ?Money
    {
        return $this->dividends;
    }

    public function getCommissions(): ?Money
    {
        return $this->commissions;
    }

    public function getConnection(): ?Money
    {
        return $this->connection;
    }

    public function getInterest(): ?Money
    {
        return $this->interest;
    }

    public function getBenefits(): ?Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): ?float
    {
        return $this->percentageBenefits;
    }
}
