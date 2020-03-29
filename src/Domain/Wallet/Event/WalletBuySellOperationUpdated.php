<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Money\Money;
use DateTime;

abstract class WalletBuySellOperationUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $dateAt;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $funds;

    /**
     * @var Money
     */
    private $benefits;

    /**
     * @var float
     */
    private $percentageBenefits;

    /**
     * @var BookEntry
     */
    private $commissions;

    public function __construct(
        string $id,
        DateTime $dateAt,
        Money $capital,
        Money $funds,
        Money $benefits,
        float $percentageBenefits,
        BookEntry $commissions
    ) {
        $this->id = $id;
        $this->dateAt = $dateAt;
        $this->capital = $capital;
        $this->funds = $funds;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->commissions = $commissions;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function getBenefits(): Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): float
    {
        return $this->percentageBenefits;
    }

    public function getCommissions(): BookEntry
    {
        return $this->commissions;
    }
}
