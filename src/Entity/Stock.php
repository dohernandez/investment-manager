<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank(message="Please enter the name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message="Please enter the symbol")
     */
    private $symbol;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=3, nullable=true)
     * @Assert\NotBlank(message="Please enter the value")
     */
    private $value;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $dividendYield;

    /**
     * @Gedmo\Timestampable(on="create")
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
     * @Assert\NotBlank(message="Please enter the market")
     */
    private $market;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StockInfo", inversedBy="stocks", cascade={"persist"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StockInfo", inversedBy="stocks", cascade={"persist"})
     */
    private $sector;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StockInfo", inversedBy="stocks", cascade={"persist"})
     */
    private $industry;

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

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDividendYield(): ?float
    {
        return $this->dividendYield;
    }

    public function setDividendYield(?float $dividendYield): self
    {
        $this->dividendYield = $dividendYield;

        return $this;
    }

    public function getLastPriceUpdate(): ?\DateTime
    {
        return $this->lastPriceUpdate;
    }

    public function setLastPriceUpdate(\DateTime $lastPriceUpdate): self
    {
        $this->lastPriceUpdate = $lastPriceUpdate;

        return $this;
    }

    public function getLastChangePrice(): ?float
    {
        return $this->lastChangePrice;
    }

    public function setLastChangePrice(?float $lastChangePrice): self
    {
        $this->lastChangePrice = $lastChangePrice;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return $this->getName() . ' ('. $this->getSymbol() .')';
    }

    public function getMarket(): ?StockMarket
    {
        return $this->market;
    }

    public function setMarket(StockMarket $market): self
    {
        $this->market = $market;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?StockInfo
    {
        return $this->type;
    }

    public function setType(?StockInfo $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSector(): ?StockInfo
    {
        return $this->sector;
    }

    public function setSector(?StockInfo $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getIndustry(): ?StockInfo
    {
        return $this->industry;
    }

    public function setIndustry(?StockInfo $industry): self
    {
        $this->industry = $industry;

        return $this;
    }

    public function setStockInfo(?StockInfo $stockInfo): self
    {
        switch ($stockInfo->getType()) {
            case StockInfo::TYPE:
                $this->setType($stockInfo);

                break;
            case StockInfo::SECTOR:
                $this->setSector($stockInfo);

                break;
            case StockInfo::INDUSTRY:
                $this->setIndustry($stockInfo);

                break;
            default:
                throw new \LogicException('type ' . $stockInfo->getType() . ' not supported');
        }

        return $this;
    }
}
