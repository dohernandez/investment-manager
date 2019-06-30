<?php

namespace App\Entity;

use App\Repository\Criteria\StockDividendByCriteria;
use App\VO\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="money", nullable=true)
     * @Assert\NotBlank(message="Please enter the value")
     *
     * @var Money
     */
    private $value;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $dividendYield;

    /**
     * @Gedmo\Timestampable(on="change", field={"value"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastPriceUpdate;

    /**
     * @ORM\Column(type="money", nullable=true)
     *
     * @var Money
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
     * @ORM\ManyToOne(targetEntity="App\Entity\StockInfo", inversedBy="typeStocks", cascade={"persist"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StockInfo", inversedBy="sectorStocks", cascade={"persist"})
     */
    private $sector;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StockInfo", inversedBy="industryStocks", cascade={"persist"})
     */
    private $industry;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StockDividend", mappedBy="stock", orphanRemoval=true, cascade={"persist"})
     */
    private $dividends;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $peRatio;

    /**
     * @ORM\Column(type="money", nullable=true)
     *
     * @var Money
     */
    private $preClose;

    /**
     * @ORM\Column(type="money", nullable=true)
     *
     * @var Money
     */
    private $open;

    /**
     * @ORM\Column(type="money", nullable=true)
     *
     * @var Money
     */
    private $dayLow;

    /**
     * @ORM\Column(type="money", nullable=true)
     *
     * @var Money
     */
    private $dayHigh;

    /**
     * @ORM\Column(name="week_52_low", type="money", nullable=true)
     *
     * @var Money
     */
    private $week52Low;

    /**
     * @ORM\Column(name="week_52_high", type="money", nullable=true)
     *
     * @var Money
     */
    private $week52High;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trade", mappedBy="stock")
     */
    private $trades;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="stock")
     */
    private $operations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Position", mappedBy="stock")
     */
    private $positions;

    public function __construct()
    {
        $this->dividends = new ArrayCollection();
        $this->trades = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->positions = new ArrayCollection();
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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getValue(): ?Money
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        $this->updateDividendYield($this->nextDividend());

        return $this;
    }

    public function updateDividendYield(?StockDividend $nextDividend = null): self
    {
        $dividendYield = null;

        if ($nextDividend !== null && $this->getValue() !== null) {
            $dividendYield = $nextDividend->getValue()->getValue() * 4 / $this->getValue()->getValue() * 100;
        }

        $this->setDividendYield($dividendYield);

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

    public function getLastChangePrice(): ?Money
    {
        return $this->lastChangePrice;
    }

    public function setLastChangePrice(?Money $lastChangePrice): self
    {
        $this->lastChangePrice = $lastChangePrice;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s (%s:%s)',
            $this->getName(),
            $this->getSymbol(),
            $this->getMarket()->getSymbol()
        );
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

    /**
     * @return Collection|StockDividend[]
     */
    public function getDividends(): Collection
    {
        return $this->dividends;
    }

    public function addDividend(StockDividend $dividend): self
    {
        if (!$this->dividends->exists(function ($key, StockDividend $d) use ($dividend) {
            if ($d->getExDate() == $dividend->getExDate()) {
                return true;
            }

            return false;
        } )) {
            $dividend->setStock($this);
            $this->dividends[] = $dividend;
        }

        return $this;
    }

    public function removeDividend(StockDividend $dividend): self
    {
        if ($this->dividends->contains($dividend)) {
            // TODO remove

            $this->dividends->removeElement($dividend);
            // set the owning side to null (unless already changed)
            if ($dividend->getStock() === $this) {
                $dividend->setStock(null);
            }
        }

        return $this;
    }

    /**
     * Returns the next dividend after time.
     *
     * @param string $time
     *
     * @return StockDividend|null
     * @throws \Exception
     */
    public function nextDividend($time='now'): ?StockDividend
    {
        $nextDate = new \DateTime($time);

        /**
         * @psalm-var Collection<TKey,StockDividend>
         * @var Collection $matches
         */
        $matches = $this->dividends->matching(StockDividendByCriteria::nextExDate($nextDate));

        if ($matches->isEmpty()){
            return null;
        }

        return $matches->first();
    }

    /**
     * Returns the last dividend before time.
     * Use in most case when nextDividend is null.
     *
     * @param string $time
     *
     * @return StockDividend|null
     * @throws \Exception
     */
    public function preDividend($time='now'): ?StockDividend
    {
        $preDate = new \DateTime($time);

        /**
         * @psalm-var Collection<TKey,StockDividend>
         * @var Collection $matches
         */
        $matches = $this->dividends->matching(StockDividendByCriteria::lastExDate($preDate));

        if ($matches->isEmpty()){
            return null;
        }

        return $matches->first();
    }

    public function getPeRatio(): ?float
    {
        return $this->peRatio;
    }

    public function setPeRatio(?float $peRatio): self
    {
        $this->peRatio = $peRatio;

        return $this;
    }

    public function getPreClose(): ?Money
    {
        return $this->preClose;
    }

    public function setPreClose(?Money $preClose): self
    {
        $this->preClose = $preClose;

        return $this;
    }

    public function getOpen(): ?Money
    {
        return $this->open;
    }

    public function setOpen(?Money $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getDayLow(): ?Money
    {
        return $this->dayLow;
    }

    public function setDayLow(?Money $dayLow): self
    {
        $this->dayLow = $dayLow;

        return $this;
    }

    public function getDayHigh(): ?Money
    {
        return $this->dayHigh;
    }

    public function setDayHigh(?Money $dayHigh): self
    {
        $this->dayHigh = $dayHigh;

        return $this;
    }

    public function getWeek52Low(): ?Money
    {
        return $this->week52Low;
    }

    public function setWeek52Low(?Money $week52Low): self
    {
        $this->week52Low = $week52Low;

        return $this;
    }

    public function getWeek52High(): ?Money
    {
        return $this->week52High;
    }

    public function setWeek52High(?Money $week52High): self
    {
        $this->week52High = $week52High;

        return $this;
    }

    /**
     * @return Collection|Trade[]
     */
    public function getTrades(): Collection
    {
        return $this->trades;
    }

    public function addTrade(Trade $trade): self
    {
        if (!$this->trades->contains($trade)) {
            $this->trades[] = $trade;
            $trade->setStock($this);
        }

        return $this;
    }

    public function removeTrade(Trade $trade): self
    {
        if ($this->trades->contains($trade)) {
            $this->trades->removeElement($trade);
            // set the owning side to null (unless already changed)
            if ($trade->getStock() === $this) {
                $trade->setStock(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Operation[]
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function addOperation(Operation $operation): self
    {
        if (!$this->operations->contains($operation)) {
            $this->operations[] = $operation;
            $operation->setStock($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getStock() === $this) {
                $operation->setStock(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Position[]
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions[] = $position;
            $position->setStock($this);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->contains($position)) {
            $this->positions->removeElement($position);
            // set the owning side to null (unless already changed)
            if ($position->getStock() === $this) {
                $position->setStock(null);
            }
        }

        return $this;
    }

    public function getChange(): ?Money
    {
        if ($this->value === null) {
            return null;
        }

        return $this->value->decrease($this->preClose);
    }

    /**
     * @return Collection|StockDividend[]
     */
    public function getProjectedAndAnnouncedDividends(): Collection
    {
        return $this->dividends->matching(StockDividendByCriteria::projectedAndAnnounced());
    }
}
