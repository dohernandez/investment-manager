<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;

class PositionBook
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var Money
     */
    private $buys;

    /**
     * @var Money
     */
    private $sells;

    /**
     * @var Money
     */
    private $averagePrice;

    /**
     * @var BookEntry|null
     */
    private $dividendPaid;

    /**
     * @var BookEntry|null
     */
    private $dividendRetention;

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
    private $toPayDividend;

    /**
     * @var float|null
     */
    private $toPayDividendYield;

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
     * @var Money|null
     */
    private $preClosed;

    /**
     * @var float|null
     */
    private $percentageChanged;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public static function create(Currency $currency): self
    {
        $self = new static();

        $self->currency = $currency;

        $self->buys = new Money($currency);
        $self->sells = new Money($currency);
        $self->benefits = new Money($currency);
        $self->averagePrice = new Money($currency);

        $self->percentageBenefits = 0;

        return $self;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getBuys(): Money
    {
        return $this->buys;
    }

    public function setBuys(Money $buys): self
    {
        $this->buys = $buys;

        return $this;
    }

    public function getSells(): Money
    {
        return $this->sells;
    }

    public function setSells(Money $sells): self
    {
        $this->sells = $sells;

        return $this;
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

    public function getDividendPaid(): ?BookEntry
    {
        return $this->dividendPaid;
    }

    public function setDividendPaid(?BookEntry $dividendPaid): self
    {
        $this->dividendPaid = $dividendPaid;

        return $this;
    }

    public function getTotalDividendPaid(): Money
    {
        if (!$this->dividendPaid) {
            return new Money($this->currency);
        }

        return $this->dividendPaid->getTotal();
    }

    public function getDividendRetention(): ?BookEntry
    {
        return $this->dividendRetention;
    }

    public function setDividendRetention(?BookEntry $dividendRetention): self
    {
        $this->dividendRetention = $dividendRetention;

        return $this;
    }

    public function getNextDividend(): ?Money
    {
        return $this->nextDividend;
    }

    public function setNextDividend(?Money $nextDividend): self
    {
        $this->nextDividend = $nextDividend;

        return $this;
    }

    public function getNextDividendYield(): ?float
    {
        return $this->nextDividendYield;
    }

    public function setNextDividendYield(?float $nextDividendYield): self
    {
        $this->nextDividendYield = $nextDividendYield;

        return $this;
    }

    public function getToPayDividend(): ?Money
    {
        return $this->toPayDividend;
    }

    public function setToPayDividend(?Money $toPayDividend): self
    {
        $this->toPayDividend = $toPayDividend;

        return $this;
    }

    public function getToPayDividendYield(): ?float
    {
        return $this->toPayDividendYield;
    }

    public function setToPayDividendYield(?float $toPayDividendYield): self
    {
        $this->toPayDividendYield = $toPayDividendYield;

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

    public function getChanged(): ?Money
    {
        return $this->changed;
    }

    public function setChanged(?Money $changed): self
    {
        $this->changed = $changed;

        return $this;
    }

    public function getPercentageChanged(): ?float
    {
        return $this->percentageChanged;
    }

    public function setPercentageChanged(?float $percentageChanged): self
    {
        $this->percentageChanged = $percentageChanged;

        return $this;
    }

    public function getPreClosed(): ?Money
    {
        return $this->preClosed;
    }

    public function setPreClosed(?Money $preClosed): self
    {
        $this->preClosed = $preClosed;

        return $this;
    }
}
