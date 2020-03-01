<?php

namespace App\Domain\Wallet\Event;

use App\Infrastructure\Money\Money;
use DateTime;

final class CloseOperationRegistered
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
    private $sells;

    /**
     * @var Money
     */
    private $benefits;

    /**
     * @var float
     */
    private $percentageBenefits;

    public function __construct(
        string $id,
        DateTime $dateAt,
        Money $sells,
        Money $benefits,
        float $percentageBenefits
    ) {
        $this->id = $id;
        $this->dateAt = $dateAt;
        $this->sells = $sells;
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

    public function getSells(): Money
    {
        return $this->sells;
    }

    public function getBenefits(): Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): float
    {
        return $this->percentageBenefits;
    }
}
