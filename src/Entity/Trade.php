<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TradeRepository")
 */
class Trade implements Entity
{
    const STATUS_OPEN = 'open';
    const STATUS_CLOSE = 'close';

    const STATUS = [self::STATUS_OPEN, self::STATUS_CLOSE];

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stock", inversedBy="trades")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stock;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="trades")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wallet;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $buyAmount;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $buyPaid;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $sellAmount;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $sellPaid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $openedAt;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $capital;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $dividend;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Choice(choices=Trade::STATUS, message="Please, choose a valid status.")
     * @Assert\NotBlank(message="Please enter the status.")
     */
    private $status;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $net;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getBuyAmount(): ?float
    {
        return $this->buyAmount;
    }

    public function setBuyAmount(?float $buyAmount): self
    {
        $this->buyAmount = $buyAmount;

        return $this;
    }

    public function getBuyPaid()
    {
        return $this->buyPaid;
    }

    public function setBuyPaid(?float $buyPaid): self
    {
        $this->buyPaid = $buyPaid;

        return $this;
    }

    public function getSellAmount(): ?float
    {
        return $this->sellAmount;
    }

    public function setSellAmount(?float $sellAmount): self
    {
        $this->sellAmount = $sellAmount;

        return $this;
    }

    public function getSellPaid(): ?float
    {
        return $this->sellPaid;
    }

    public function setSellPaid(?float $sellPaid): self
    {
        $this->sellPaid = $sellPaid;

        return $this;
    }

    public function getOpenedAt(): ?\DateTimeInterface
    {
        return $this->openedAt;
    }

    public function setOpenedAt(\DateTimeInterface $openedAt): self
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCapital(): ?float
    {
        return $this->capital;
    }

    public function setCapital(?float $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getDividend(): ?float
    {
        return $this->dividend;
    }

    public function setDividend(?float $dividend): self
    {
        $this->dividend = $dividend;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNet(): ?float
    {
        return $this->net;
    }

    public function setNet(?float $net): self
    {
        $this->net = $net;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return sprintf(
            '%d. %s:%s - %d',
            $this->number,
            $this->getStock()->getSymbol(),
            $this->getStock()->getMarket()->getSymbol(),
            $this->amount
        );
    }
}
