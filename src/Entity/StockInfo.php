<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StockInfoRepository")
 */
class StockInfo implements Entity
{
    const TYPE = 'type';
    const SECTOR = 'sector';
    const INDUSTRY = 'industry';

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Stock", mappedBy="type")
     */
    private $typeStocks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Stock", mappedBy="sector")
     */
    private $sectorStocks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Stock", mappedBy="industry")
     */
    private $industryStocks;

    public function __construct()
    {
        // Work around because Doctrine's official docs: you can't add multiple mappedBy columns.
        $this->typeStocks = new ArrayCollection();
        $this->sectorStocks = new ArrayCollection();
        $this->industryStocks = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Stock[]
     */
    public function getStocks(): Collection
    {
        return $this->typeStocks + $this->sectorStocks + $this->industryStocks;
    }

    public function addStock(Stock $stock): self
    {
        switch ($this->getType()) {
            case StockInfo::TYPE:
                if (!$this->typeStocks->contains($stock)) {
                    $this->typeStocks[] = $stock;
                    $stock->setType($this);
                }

                break;
            case StockInfo::SECTOR:
                if (!$this->sectorStocks->contains($stock)) {
                    $this->sectorStocks[] = $stock;
                    $stock->setSector($this);
                }

                break;
            case StockInfo::INDUSTRY:
                if (!$this->industryStocks->contains($stock)) {
                    $this->industryStocks[] = $stock;
                    $stock->setIndustry($this);
                }

                break;
            default:
                throw new \LogicException('type ' . $this->getType() . ' not supported');
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->typeStocks->contains($stock)) {
            $this->typeStocks->removeElement($stock);
            // set the owning side to null (unless already changed)
            if ($stock->getType() === $this) {
                $stock->setType(null);
            }
        }

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
