<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Money;
use DateTime;

class PositionBook
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Money
     */
    private $averagePrice;

    /**
     * @var BookMetadata|null
     */
    private $dividendPaid;

    /**
     * @var BookMetadata|null
     */
    private $dividendRetention;

    /**
     * @var Money
     */
    private $dividend;

    /**
     * @var float
     */
    private $dividendYield;

    /**
     * @var DateTime
     */
    private $dividendExDate;

    /**
     * @var Money
     */
    private $dividendToPay;

    /**
     * @var float
     */
    private $dividendToPayYield;

    /**
     * @var DateTime
     */
    private $dividendPaymentDate;

    /**
     * @var Money
     */
    private $stockPrice;

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
    private $changed;

    /**
     * @var float
     */
    private $percentageChanged;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getAveragePrice(): Money
    {
        return $this->averagePrice;
    }

    public function setAveragePrice(Money $averagePrice): self
    {
        $this->averagePrice = $averagePrice;

        return $this;
    }

    public function getDividendPaid(): ?BookMetadata
    {
        return $this->dividendPaid;
    }

    public function setDividendPaid(?BookMetadata $dividendPaid): self
    {
        $this->dividendPaid = $dividendPaid;

        return $this;
    }

    public function getDividendRetention(): ?BookMetadata
    {
        return $this->dividendRetention;
    }

    public function setDividendRetention(?BookMetadata $dividendRetention): self
    {
        $this->dividendRetention = $dividendRetention;

        return $this;
    }

    public function getDividend(): Money
    {
        return $this->dividend;
    }

    public function setDividend(Money $dividend): self
    {
        $this->dividend = $dividend;

        return $this;
    }

    public function getDividendYield(): float
    {
        return $this->dividendYield;
    }

    public function setDividendYield(float $dividendYield): self
    {
        $this->dividendYield = $dividendYield;

        return $this;
    }

    public function getDividendExDate(): DateTime
    {
        return $this->dividendExDate;
    }

    public function setDividendExDate(DateTime $dividendExDate): self
    {
        $this->dividendExDate = $dividendExDate;

        return $this;
    }

    public function getDividendToPay(): Money
    {
        return $this->dividendToPay;
    }

    public function setDividendToPay(Money $dividendToPay): self
    {
        $this->dividendToPay = $dividendToPay;

        return $this;
    }

    public function getDividendToPayYield(): float
    {
        return $this->dividendToPayYield;
    }

    public function setDividendToPayYield(float $dividendToPayYield): self
    {
        $this->dividendToPayYield = $dividendToPayYield;

        return $this;
    }

    public function getDividendPaymentDate(): DateTime
    {
        return $this->dividendPaymentDate;
    }

    public function setDividendPaymentDate(DateTime $dividendPaymentDate): self
    {
        $this->dividendPaymentDate = $dividendPaymentDate;

        return $this;
    }

    public function getStockPrice(): Money
    {
        return $this->stockPrice;
    }

    public function setStockPrice(Money $stockPrice): self
    {
        $this->stockPrice = $stockPrice;

        return $this;
    }

    public function getBenefits(): Money
    {
        return $this->benefits;
    }

    public function setBenefits(Money $benefits): self
    {
        $this->benefits = $benefits;

        return $this;
    }

    public function getPercentageBenefits(): float
    {
        return $this->percentageBenefits;
    }

    public function setPercentageBenefits(float $percentageBenefits): self
    {
        $this->percentageBenefits = $percentageBenefits;

        return $this;
    }

    public function getChanged(): Money
    {
        return $this->changed;
    }

    public function setChanged(Money $changed): self
    {
        $this->changed = $changed;

        return $this;
    }

    public function getPercentageChanged(): float
    {
        return $this->percentageChanged;
    }

    public function setPercentageChanged(float $percentageChanged): self
    {
        $this->percentageChanged = $percentageChanged;

        return $this;
    }
}
