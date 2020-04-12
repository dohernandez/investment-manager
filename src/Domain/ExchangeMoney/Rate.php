<?php

namespace App\Domain\ExchangeMoney;


use App\Infrastructure\Money\Currency;
use DateTime;
use Gedmo\Timestampable\Traits\Timestampable;

class Rate
{
    use Timestampable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Currency
     */
    private $fromCurrency;

    /**
     * @var Currency
     */
    private $toCurrency;

    /**
     * @var float
     */
    private $rate;

    /**
     * @var string
     */
    private $paarCurrency;

    /**
     * @var DateTime
     */
    private $dateAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromCurrency(): ?Currency
    {
        return $this->fromCurrency;
    }

    public function setFromCurrency(Currency $fromCurrency): self
    {
        $this->fromCurrency = $fromCurrency;

        return $this;
    }

    public function getToCurrency(): ?Currency
    {
        return $this->toCurrency;
    }

    public function setToCurrency(Currency $toCurrency): self
    {
        $this->toCurrency = $toCurrency;

        return $this;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getPaarCurrency(): string
    {
        return $this->paarCurrency;
    }

    public function setPaarCurrency(string $paarCurrency): self
    {
        $this->paarCurrency = $paarCurrency;

        return $this;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }

    public function setDateAt(DateTime $dateAt): self
    {
        $this->dateAt = $dateAt;

        return $this;
    }
}
