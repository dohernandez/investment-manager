<?php

namespace App\Domain\Report\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;

final class Wallet implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $slug;

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
        string $id,
        string $name,
        string $slug,
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
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
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
