<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WalletRepository")
 */
class Wallet implements Entity
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Broker", inversedBy="wallets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $broker;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

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
}
