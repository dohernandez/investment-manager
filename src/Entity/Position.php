<?php

namespace App\Entity;

use App\Repository\Criteria\TradeByCriteria;
use App\VO\Money;
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
     * @var Money
     *
     * @ORM\Column(type="money")
     */
    private $invested;

    /**
     * @var Money
     *
     * @ORM\Column(type="money")
     */
    private $dividend;

    /**
     * @var Money
     *
     * @ORM\Column(type="money")
     */
    private $buy;

    /**
     * @var Money
     *
     * @ORM\Column(type="money")
     */
    private $sell;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="position")
     */
    private $operations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trade", mappedBy="position", cascade={"persist"})
     */
    private $trades;

    /**
     * In percentage
     *
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
     * @var Wallet
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wallet;

    /**
     * @ORM\Column(type="datetime")
     */
    private $openedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $capital;

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

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function setInvested(?Money $invested): self
    {
        if ($invested === null) {
            $invested = Money::fromCurrency($this->getWallet()->getCurrency());
        }

        $this->invested = $invested;

        return $this;
    }

    public function increaseInvested(?Money $invested): self
    {
        if ($invested === null) {
            return $this;
        }

        $this->setInvested($this->getInvested()->increase($invested));

        return $this;
    }

    public function decreaseInvested(?Money $invested): self
    {
        if ($invested === null) {
            return $this;
        }

        $this->setInvested($this->getInvested()->decrease($invested));

        return $this;
    }

    public function getDividend(): Money
    {
        return $this->dividend;
    }

    public function setDividend(?Money $dividend): self
    {
        if ($dividend === null) {
            $dividend = Money::fromCurrency($this->getWallet()->getCurrency());
        }

        $this->dividend = $dividend;

        return $this;
    }

    public function increaseDividend(?Money $dividend): self
    {
        if ($dividend === null) {
            return $this;
        }

        $this->setDividend($this->getDividend()->increase($dividend));

        return $this;
    }

    public function getBuy(): Money
    {
        return $this->buy;
    }

    public function setBuy(?Money $buy): self
    {
        if ($buy === null) {
            $buy = Money::fromCurrency($this->getWallet()->getCurrency());
        }

        $this->buy = $buy;

        return $this;
    }

    public function increaseBuy(?Money $buy): self
    {
        if ($buy === null) {
            return $this;
        }

        $this->setBuy($this->getBuy()->increase($buy));

        return $this;
    }

    public function getSell(): Money
    {
        return $this->sell;
    }

    public function setSell(?Money $sell): self
    {
        if ($sell === null) {
            $sell = Money::fromCurrency($this->getWallet()->getCurrency());
        }

        $this->sell = $sell;

        return $this;
    }

    public function increaseSell(?Money $sell): self
    {
        if ($sell === null) {
            return $this;
        }

        $this->setSell($this->getSell()->increase($sell));

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
     *
     * @param Operation $operation
     *
     * @return Position
     */
    public function addOperation(Operation $operation): self
    {
        if (!$this->operations->contains($operation)) {
            $netValue = $operation->getNetValue();

            switch ($operation->getType()) {
                case Operation::TYPE_BUY:
                    $this->addBuy($operation->getAmount(), $netValue);
                    break;
                case Operation::TYPE_SELL:
                    $this->addSell($operation->getAmount(), $netValue);
                    break;
                case Operation::TYPE_DIVIDEND:
                    $this->addDividend($netValue);
                    break;
            }

            $this->operations[] = $operation;
            $operation->setPosition($this);
        }

        return $this;
    }

    public function addBuy(float $amount, Money $paid): self
    {
        $this->increaseInvested($paid);
        $this->increaseBuy($paid);
        $this->increaseAmount($amount);

        return $this;
    }

    public function addSell(float $amount, Money $paid): self
    {
        $this->decreaseInvested($paid);
        $this->increaseSell($paid);
        $this->decreaseAmount($amount);

        return $this;
    }

    /**
     * Alias of increaseDividend
     *
     * @param Money|null $dividend
     *
     * @return Position
     */
    public function addDividend(?Money $dividend): self
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

    /**
     * @return Collection|Trade[]
     */
    public function getOpenTrades(): Collection
    {
        return $this->trades->matching(TradeByCriteria::byStatus(self::STATUS_OPEN));
    }

    /**
     * @param \DateTimeInterface $dateAt
     *
     * @return Collection|Trade[]
     */
    public function getTradesApplyDividend(\DateTimeInterface $dateAt): Collection
    {
        return $this->trades->matching(TradeByCriteria::applyDividend($this->getStock(), $dateAt));
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

    public function getDividendRetention(): float
    {
        return $this->dividendRetention;
    }

    public function setDividendRetention(?float $dividendRetention): self
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

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): self
    {
        $this->wallet = $wallet;

        $currency = $this->getWallet()->getCurrency();

        $this->setInvested(Money::fromCurrency($currency));
        $this->setBuy(Money::fromCurrency($currency));
        $this->setSell(Money::fromCurrency($currency));
        $this->setDividend(Money::fromCurrency($currency));

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

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getCommissions(): ?float
    {
        $commissions = 0;

        foreach ($this->getOperations() as $operation) {
            $commissions += $operation->getFinalCommissionPaid();
        }

        return $commissions;
    }

    public function getBenefits(): ?Money
    {
        return $this->getCapital()
                ->increase($this->getSell())
                ->increase($this->getDividend())
                ->decrease($this->getBuy())
            ;
    }

    public function getPercentageBenefits(): ?float
    {
        $percentage = $this->getBenefits()->getValue() * 100 / $this->getBuy()->getValue();

        return $percentage;
    }

    public function getChange(): ?float
    {
        return $this->getAmount() * $this->getStock()->getChange()->getValue();
    }

    public function getPreClose(): ?float
    {
        return $this->getAmount() * $this->getStock()->getPreClose()->getValue();
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function setCapital(Money $capital): void
    {
        $this->capital = $capital;
    }
}
