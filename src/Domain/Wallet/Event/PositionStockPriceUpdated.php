<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Operation;
use App\Domain\Wallet\Stock;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class PositionStockPriceUpdated implements DataInterface
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
        Money $capital,
        Money $benefits,
        float $percentageBenefits,
        ?Money $change = null,
        ?float $percentageChange = null,
        ?Money $preClose = null,
        ?Money $nextDividend = null,
        ?float $nextDividendYield = null,
        ?Money $nextDividendAfterTaxes = null,
        ?float $nextDividendYieldAfterTaxes = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->stock = $stock;
        $this->capital = $capital;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->change = $change;
        $this->percentageChange = $percentageChange;
        $this->preClose = $preClose;
        $this->updatedAt = $updatedAt;
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
