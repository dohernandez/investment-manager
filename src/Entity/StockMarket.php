<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StockMarketRepository")
 */
class StockMarket implements Entity
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
     * @Assert\NotBlank(message="Please enter the name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter the country")
     * @Assert\Country(message="Please enter a valid country")
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     * @Assert\NotBlank(message="Please enter the symbol")
     */
    private $symbol;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     * @Assert\NotBlank(message="Please enter the yahoo symbol")
     */
    private $yahoo_symbol;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Stock", mappedBy="market", orphanRemoval=true)
     */
    private $stocks;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCountryName(): ?string
    {
        return Intl::getRegionBundle()->getCountryName($this->getCountry());
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

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

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return $this->getSymbol() . ' - ' . $this->getName();
    }

    /**
     * @return Collection|Stock[]
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks[] = $stock;
            $stock->setMarket($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->contains($stock)) {
            $this->stocks->removeElement($stock);
            // set the owning side to null (unless already changed)
            if ($stock->getMarket() === $this) {
                $stock->setMarket(null);
            }
        }

        return $this;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahoo_symbol;
    }

    public function setYahooSymbol(?string $yahoo_symbol): self
    {
        $this->yahoo_symbol = $yahoo_symbol;

        return $this;
    }
}
