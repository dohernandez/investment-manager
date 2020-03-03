<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;

class WalletBook
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
     * @var Money|null
     */
    private $invested;

    /**
     * @var Money|null
     */
    private $capital;

    /**
     * @var Money|null
     */
    private $funds;

    /**
     * @var BookEntry|null
     */
    private $dividends;

    /**
     * @var BookEntry|null
     */
    private $commissions;

    /**
     * @var BookEntry|null
     */
    private $connection;

    /**
     * @var BookEntry|null
     */
    private $interest;

    /**
     * @var Money|null
     */
    private $benefits;

    /**
     * @var float|null
     */
    private $percentageBenefits;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public static function createWithInitialBalance(Currency $currency, ?Money $balance): self
    {
        $self = new static();

        $self->currency = $currency;

        $self->invested = $balance ?? new Money($currency);
        $self->funds = $balance ?? new Money($currency);

        $self->capital = new Money($currency);
        $self->benefits = new Money($currency);

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

    public function getInvested(): ?Money
    {
        return $this->invested;
    }

    public function setInvested(?Money $invested): self
    {
        $this->invested = $invested;

        return $this;
    }

    public function getCapital(): ?Money
    {
        return $this->capital;
    }

    public function setCapital(?Money $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getFunds(): ?Money
    {
        return $this->funds;
    }

    public function setFunds(?Money $funds): self
    {
        $this->funds = $funds;

        return $this;
    }

    public function getDividends(): ?BookEntry
    {
        return $this->dividends;
    }

    public function setDividends(?BookEntry $dividends): self
    {
        $this->dividends = $dividends;

        return $this;
    }

    public function getCommissions(): ?BookEntry
    {
        return $this->commissions;
    }

    public function setCommissions(?BookEntry $commissions): self
    {
        $this->commissions = $commissions;

        return $this;
    }

    public function getConnection(): ?BookEntry
    {
        return $this->connection;
    }

    public function setConnection(?BookEntry $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function getInterest(): ?BookEntry
    {
        return $this->interest;
    }

    public function setInterest(?BookEntry $interest): self
    {
        $this->interest = $interest;

        return $this;
    }

    public function getBenefits(): ?Money
    {
        return $this->benefits;
    }

    public function setBenefits(?Money $benefits): self
    {
        $this->benefits = $benefits;

        return $this;
    }

    public function getPercentageBenefits(): ?float
    {
        return $this->percentageBenefits;
    }

    public function setPercentageBenefits(?float $percentageBenefits): self
    {
        $this->percentageBenefits = $percentageBenefits;

        return $this;
    }
}
