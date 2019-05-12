<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StockRepository")
 */
class Stock implements Entity
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $symbol;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=3, nullable=true)
     */
    private $value;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $dividendYield;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastPriceUpdate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $lastChangePrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StockMarket", inversedBy="stocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $market;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDividendYield()
    {
        return $this->dividendYield;
    }

    public function setDividendYield($dividendYield): self
    {
        $this->dividendYield = $dividendYield;

        return $this;
    }

    public function getLastPriceUpdate()
    {
        return $this->lastPriceUpdate;
    }

    public function setLastPriceUpdate($lastPriceUpdate): self
    {
        $this->lastPriceUpdate = $lastPriceUpdate;

        return $this;
    }

    public function getLastChangePrice()
    {
        return $this->lastChangePrice;
    }

    public function setLastChangePrice($lastChangePrice): self
    {
        $this->lastChangePrice = $lastChangePrice;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    public function getMarket(): ?StockMarket
    {
        return $this->market;
    }

    public function setMarket(?StockMarket $market): self
    {
        $this->market = $market;

        return $this;
    }
}
