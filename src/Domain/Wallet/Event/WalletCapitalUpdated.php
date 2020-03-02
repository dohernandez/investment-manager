<?php

namespace App\Domain\Wallet\Event;

use App\Infrastructure\Money\Money;
use DateTime;

class WalletCapitalUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Money
     */
    private $benefits;

    /**
     * @var float
     */
    private $percentageBenefits;

    /**
     * @var Money
     */
    private $capital;

    public function __construct(
        string $id,
        Money $capital,
        Money $benefits,
        float $percentageBenefits
    ) {
        $this->id = $id;
        $this->capital = $capital;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }

    public function getBenefits(): Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): float
    {
        return $this->percentageBenefits;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }
}
