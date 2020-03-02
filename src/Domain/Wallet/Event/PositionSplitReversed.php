<?php

namespace App\Domain\Wallet\Event;

use App\Infrastructure\Money\Money;

final class PositionSplitReversed
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var Money
     */
    private $averagePrice;

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

    public function __construct(
        string $id,
        int $amount,
        Money $averagePrice,
        Money $capital,
        Money $benefits,
        float $percentageBenefits
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->averagePrice = $averagePrice;
        $this->capital = $capital;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getAveragePrice(): Money
    {
        return $this->averagePrice;
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
}
