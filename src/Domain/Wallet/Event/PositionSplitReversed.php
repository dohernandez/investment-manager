<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Stock;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;

final class PositionSplitReversed implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Stock|null
     */
    private $stock;

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
    private $nextDividendAfterTaxes;

    /**
     * @var float|null
     */
    private $nextDividendYieldAfterTaxes;

    public function __construct(
        string $id,
        Stock $stock,
        int $amount,
        Money $averagePrice,
        Money $capital,
        Money $benefits,
        float $percentageBenefits,
        ?Money $changed = null,
        ?float $percentageChanged = null,
        ?Money $preClosed = null,
        ?Money $nextDividend = null,
        ?float $nextDividendYield = null,
        ?Money $nextDividendAfterTaxes = null,
        ?float $nextDividendYieldAfterTaxes = null
    ) {
        $this->id = $id;
        $this->stock = $stock;
        $this->amount = $amount;
        $this->averagePrice = $averagePrice;
        $this->capital = $capital;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->changed = $changed;
        $this->percentageChanged = $percentageChanged;
        $this->preClosed = $preClosed;
        $this->nextDividend = $nextDividend;
        $this->nextDividendYield = $nextDividendYield;
        $this->nextDividendAfterTaxes = $nextDividendAfterTaxes;
        $this->nextDividendYieldAfterTaxes = $nextDividendYieldAfterTaxes;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
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

    public function getNextDividend(): ?Money
    {
        return $this->nextDividend;
    }

    public function getNextDividendYield(): ?float
    {
        return $this->nextDividendYield;
    }

    public function getNextDividendAfterTaxes(): ?Money
    {
        return $this->nextDividendAfterTaxes;
    }

    public function getNextDividendYieldAfterTaxes(): ?float
    {
        return $this->nextDividendYieldAfterTaxes;
    }
}
