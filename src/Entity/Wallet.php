<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WalletRepository")
 */
class Wallet implements Entity
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=false)
     */
    private $invested = 0;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=false)
     */
    private $capital = 0;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=false)
     */
    private $funds = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Broker", inversedBy="wallet")
     * @ORM\JoinColumn(nullable=false)
     */
    private $broker;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trade", mappedBy="wallet")
     */
    private $trades;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=false)
     */
    private $dividend = 0;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=4, nullable=false)
     */
    private $commissions = 0;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=false)
     */
    private $connection = 0;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=false)
     */
    private $interest = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="wallet")
     */
    private $operations;

    public function __construct()
    {
        $this->trades = new ArrayCollection();
        $this->operations = new ArrayCollection();
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

    public function getInvested(): float
    {
        return $this->invested;
    }

    public function setInvested(float $invested): self
    {
        $this->invested = $invested;

        return $this;
    }

    public function getCapital(): float
    {
        return $this->capital;
    }

    public function setCapital(float $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getFunds(): float
    {
        return $this->funds;
    }

    public function setFunds(float $funds): self
    {
        $this->funds = $funds;

        return $this;
    }

    public function getBroker(): ?Broker
    {
        return $this->broker;
    }

    public function setBroker(?Broker $broker): self
    {
        $this->broker = $broker;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    public function addFunds(float $funds): self
    {
        $this->setCapital($this->getCapital() + $funds);
        $this->setFunds($this->getFunds() + $funds);

        return $this;
    }

    public function subtractFunds(float $funds): self
    {
        $this->setCapital($this->getCapital() - $funds);
        $this->setFunds($this->getFunds() - $funds);

        return $this;
    }

    public function getBenefits(): float
    {
        return $this->getCapital() + $this->getFunds() - $this->getInvested();
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
            $trade->setWallet($this);
        }

        return $this;
    }

    public function removeTrade(Trade $trade): self
    {
        if ($this->trades->contains($trade)) {
            $this->trades->removeElement($trade);
            // set the owning side to null (unless already changed)
            if ($trade->getWallet() === $this) {
                $trade->setWallet(null);
            }
        }

        return $this;
    }

    public function getDividend(): float
    {
        return $this->dividend;
    }

    public function setDividend(float $dividend): self
    {
        $this->dividend = $dividend;

        return $this;
    }

    public function getCommissions(): float
    {
        return $this->commissions;
    }

    public function setCommissions(float $commissions): self
    {
        $this->commissions = $commissions;

        return $this;
    }

    public function getConnection(): float
    {
        return $this->connection;
    }

    public function setConnection(float $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function getInterest(): float
    {
        return $this->interest;
    }

    public function setInterest(float $interest): self
    {
        $this->interest = $interest;

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
            $operation->setWallet($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getWallet() === $this) {
                $operation->setWallet(null);
            }
        }

        return $this;
    }
}
