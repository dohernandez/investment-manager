<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Operation;
use App\Infrastructure\Money\Money;
use DateTime;

final class PositionStockPriceUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $benefits;

    /**
     * @var float
     */
    private $percentageBenefits;

    /**
     * @var Money|null
     */
    private $change;

    /**
     * @var float|null
     */
    private $percentageChange;

    /**
     * @var Money|null
     */
    private $preClose;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    public function __construct(
        string $id,
        Money $capital,
        Money $benefits,
        float $percentageBenefits,
        ?Money $change = null,
        ?float $percentageChange = null,
        ?Money $preClose = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->capital = $capital;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->change = $change;
        $this->percentageChange = $percentageChange;
        $this->preClose = $preClose;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getBenefits(): Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): float
    {
        return $this->percentageBenefits;
    }

    public function getChange(): ?Money
    {
        return $this->change;
    }

    public function getPercentageChange(): ?float
    {
        return $this->percentageChange;
    }

    public function getPreClose(): ?Money
    {
        return $this->preClose;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
