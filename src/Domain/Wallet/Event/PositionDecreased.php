<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Operation;
use App\Infrastructure\Money\Money;

final class PositionDecreased
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
    private $invested;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $averagePrice;

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

    /**
     * @var Money|null
     */
    private $nextDividend;

    /**
     * @var float|null
     */
    private $nextDividendYield;

    /**
     * @var Money|null
     */
    private $changed;

    /**
     * @var float|null
     */
    private $percentageChanged;

    /**
     * @var Money|null
     */
    private $preClosed;

    public function __construct(
        string $id,
        int $amount,
        Money $invested,
        Money $capital,
        Money $averagePrice,
        Money $sells,
        Money $benefits,
        float $percentageBenefits,
        ?Money $changed = null,
        ?float $percentageChanged = null,
        ?Money $preClosed = null,
        ?Money $nextDividend = null,
        ?float $nextDividendYield = null
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->invested = $invested;
        $this->capital = $capital;
        $this->averagePrice = $averagePrice;
        $this->sells = $sells;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->nextDividend = $nextDividend;
        $this->nextDividendYield = $nextDividendYield;
        $this->changed = $changed;
        $this->percentageChanged = $percentageChanged;
        $this->preClosed = $preClosed;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getAveragePrice(): Money
    {
        return $this->averagePrice;
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

    public function getNextDividend(): ?Money
    {
        return $this->nextDividend;
    }

    public function getNextDividendYield(): ?float
    {
        return $this->nextDividendYield;
    }

    public function getChanged(): ?Money
    {
        return $this->changed;
    }

    public function getPercentageChanged(): ?float
    {
        return $this->percentageChanged;
    }

    public function getPreClosed(): ?Money
    {
        return $this->preClosed;
    }
}