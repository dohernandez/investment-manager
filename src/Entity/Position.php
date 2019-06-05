<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PositionRepository")
 */
class Position implements Entity
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Stock", inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stock;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $invested;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=4, nullable=true)
     */
    private $dividend;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $buy;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $sell;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="position")
     */
    private $operations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trade", mappedBy="position")
     */
    private $trades;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=4, nullable=true)
     */
    private $dividendRetention;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Choice(choices=Position::STATUS, message="Please, choose a valid status.")
     * @Assert\NotBlank(message="Please enter the status.")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wallet;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->trades = new ArrayCollection();
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function increaseAmount(int $amount): self
    {
        $this->amount += $amount;

        return $this;
    }

    public function decreaseAmount(int $amount): self
    {
        $this->amount -= $amount;

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

    public function increaseInvested(float $invested): self
    {
        $this->invested += $invested;

        return $this;
    }

    public function decreaseInvested(float $invested): self
    {
        $this->invested -= $invested;

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

    public function getBuy(): ?float
    {
        return $this->buy;
    }

    public function setBuy(?float $buy): self
    {
        $this->buy = $buy;

        return $this;
    }

    public function increaseBuy(float $buy): self
    {
        $this->buy += $buy;

        return $this;
    }

    public function getSell(): ?float
    {
        return $this->sell;
    }

    public function setSell(?float $sell): self
    {
        $this->sell = $sell;

        return $this;
    }

    public function increaseSell(float $sell): self
    {
        $this->sell += $sell;

        return $this;
    }

    /**
     * @return Collection|Operation[]
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    /**
     * Add an operation to the collection and update the position based on the operation type.
     * @param Operation $operation
     *
     * @return Position
     */
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
                case Operation::TYPE_DIVIDEND:
                    $this->addDividend($operation->getNetValue());
                    break;
            }

            $this->operations[] = $operation;
            $operation->setPosition($this);
        }

        return $this;
    }

    public function addBuy(float $amount, float $paid): self
    {
        $this->increaseInvested($paid);
        $this->increaseBuy($paid);
        $this->increaseAmount($amount);

        return $this;
    }

    public function addSell(float $amount, float $paid): self
    {
        $this->decreaseInvested($paid);
        $this->increaseSell($paid);
        $this->decreaseAmount($amount);

        return $this;
    }

    public function addDividend(float $dividend): self
    {
        $this->increaseDividend($dividend);

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getPosition() === $this) {
                $operation->setPosition(null);
            }
        }

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
            $trade->setPosition($this);
        }

        return $this;
    }

    public function removeTrade(Trade $trade): self
    {
        if ($this->trades->contains($trade)) {
            $this->trades->removeElement($trade);
            // set the owning side to null (unless already changed)
            if ($trade->getPosition() === $this) {
                $trade->setPosition(null);
            }
        }

        return $this;
    }

    public function getDividendRetention()
    {
        return $this->dividendRetention;
    }

    public function setDividendRetention($dividendRetention): self
    {
        $this->dividendRetention = $dividendRetention;

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

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return $this->getId();
    }

    public function getCapital(): float
    {
        $stock = $this->getStock();
        if ($stock === null) {
            return 0;
        }

        return $this->getAmount() * $stock->getValue();
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
}
