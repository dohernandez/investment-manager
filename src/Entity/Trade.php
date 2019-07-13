<?php

namespace App\Entity;

use App\VO\Money;
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
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $buyPaid;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $sellAmount;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
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
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $invested;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $capital;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
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
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $net = 0;

    /**
     * @var Position
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Position", inversedBy="trades")
     * @ORM\JoinColumn(nullable=false)
     */
    private $position;

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

    public function setBuyPaid(?Money $buyPaid): self
    {
        $this->buyPaid = $buyPaid;

        return $this;
    }

    public function increaseBuyPaid(Money $buyPaid): self
    {
        if ($this->buyPaid !== null) {
            $this->buyPaid = $this->buyPaid->increase($buyPaid);
        } else {
            $this->buyPaid = $buyPaid;
        }

        return $this;
    }

    public function addBuy(float $amount, Money $paid): self
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

    public function getSellPaid(): ?Money
    {
        return $this->sellPaid;
    }

    public function setSellPaid(?Money $sellPaid): self
    {
        $this->sellPaid = $sellPaid;

        return $this;
    }

    public function increaseSellPaid(Money $sellPaid): self
    {
        if ($this->sellPaid !== null) {
            $this->sellPaid = $this->sellPaid->increase($sellPaid);
        } else {
            $this->sellPaid = $sellPaid;
        }

        return $this;
    }

    public function addSell(float $amount, Money $paid): self
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

    public function getCapital(): ?Money
    {
        return $this->capital;
    }

    public function setCapital(?float $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getDividend(): ?Money
    {
        return $this->dividend;
    }

    public function setDividend(?Money $dividend): self
    {
        $this->dividend = $dividend;

        return $this;
    }

    public function increaseDividend(Money $dividend): self
    {
        if ($this->dividend !== null) {
            $this->dividend = $this->dividend->increase($dividend);
        } else {
            $this->dividend = $dividend;
        }

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

    public function getNet(): ?Money
    {
        return $this->net;
    }

    public function setNet(?Money $net): self
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
            '%s:%s - %d [%s]',
            $this->getStock()->getSymbol(),
            $this->getStock()->getMarket()->getSymbol(),
            $this->getAmount(),
            $this->getNet()
        );
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Calc the percentage the trade represent from the position.
     *
     * @param Operation $operation
     *
     * @return float|int
     */
    public function getPercentageRepresent(Operation $operation): float
    {
        // Calc position amount, at this point, the amount of the operation was already
        // subtract from the position, therefore to get the real actual position to calculate
        // the percentage above, we need to sum back the amount subtracted by the operation.
        $aPosition = ($this->position->getAmount() + $operation->getAmount());

        // Calc how much in percentage represents the amount of stocks in the trade
        // compare against the whole position
        return $this->getAmount() * 100 / $aPosition;
    }
}
