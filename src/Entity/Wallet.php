<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WalletRepository")
 */
class Wallet
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

    public function getInvested()
    {
        return $this->invested;
    }

    public function setInvested($invested): self
    {
        $this->invested = $invested;

        return $this;
    }

    public function getCapital()
    {
        return $this->capital;
    }

    public function setCapital($capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getFunds()
    {
        return $this->funds;
    }

    public function setFunds($funds): self
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
}
