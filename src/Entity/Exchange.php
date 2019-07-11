<?php

namespace App\Entity;

use App\VO\Currency;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExchangeRepository")
 */
class Exchange
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Currency
     *
     * @ORM\Column(type="currency")
     */
    private $fromCurrency;

    /**
     * @var Currency
     *
     * @ORM\Column(type="currency")
     */
    private $toCurrency;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=4, nullable=true)
     */
    private $rate;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $paarCurrency;

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

    public function setRate($rate): self
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
}
