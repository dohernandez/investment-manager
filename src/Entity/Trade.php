<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $buyPaid;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $sellAmount;

    /**
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
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
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
     *
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $invested = 0;

    /**
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
     *
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $capital = 0;

    /**
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
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
     * Final benefits returns
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
     *
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $net = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="trade")
     */
    private $operations;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function increaseBuyAmount(?float $buyAmount): self
    {
        $this->buyAmount += $buyAmount;

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

    public function increaseBuyPaid(float $buyPaid): self
    {
        $this->buyPaid += $buyPaid;

        return $this;
    }

    public function addBuy(float $amount, float $paid): self
    {
        $this->increaseBuyPaid($paid);
        $this->increaseBuyAmount($amount);
        $this->increaseAmount($amount);

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

    public function increaseSellAmount(float $sellAmount): self
    {
        $this->sellAmount += $sellAmount;

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

    public function increaseSellPaid(float $sellPaid): self
    {
        $this->sellPaid += $sellPaid;

        return $this;
    }

    public function addSell(float $amount, float $paid): self
    {
        $this->increaseSellPaid($paid);
        $this->increaseSellAmount($amount);
        $this->decreaseAmount($amount);

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

    public function increaseAmount(float $amount): self
    {
        $this->amount += $amount;

        return $this;
    }

    public function decreaseAmount(float $amount): self
    {
        $this->amount -= $amount;

        return $this;
    }

    public function getCapital(): float
    {
        $stock = $this->getStock();
        if ($stock === null) {
            return 0;
        }

        return $this->getAmount() * $stock->getValue();
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

    public function increaseDividend(float $dividend): self
    {
        $this->dividend += $dividend;

        return $this;
    }

    public function addDividend(float $dividend): self
    {
        $this->increaseDividend($dividend);

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

            switch ($operation->getType()) {
                case Operation::TYPE_BUY:
                    $this->addBuy($operation->getAmount(), $operation->getNetValue());
                    break;
                case Operation::TYPE_SELL:
                    $this->addSell($operation->getAmount(), $operation->getNetValue());
                    break;
            }

            $this->operations[] = $operation;
            $operation->setTrade($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getTrade() === $this) {
                $operation->setTrade(null);
            }
        }

        return $this;
    }
}
