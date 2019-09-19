<?php

namespace App\Entity;

use App\Repository\Criteria\TradeByCriteria;
use App\VO\Money;
use App\VO\PositionMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use mysql_xdevapi\Exception;
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
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
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

    /**
     * @var PositionMetadata
     *
     * @ORM\Column(type="position_metadata", nullable=true)
     */
    private $metadata;

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

        if ($this->amount < 0) {
            $this->amount = 0;
        }

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

        if ($this->getInvested()->getValue() < 0) {
            $this->setInvested(Money::fromCurrency(
                $this->getInvested()->getCurrency(),
                $this->getInvested()->getPrecision()
            ));
        }

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
                case Operation::TYPE_SPLIT_REVERSE:
                    $this->setAmount($operation->getAmount());

                    // Because this operation comes without value, we set the value to 0 at this point.
                    $operation->setValue(Money::fromCurrency($this->getWallet()->getCurrency()));
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

    public function getDividendRetention(): ?Money
    {
        return $this->dividendRetention;
    }

    public function setDividendRetention(?Money $dividendRetention): self
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
        $benefits = $this->getSell()
                ->increase($this->getDividend())
                ->decrease($this->getBuy())
        ;

        if ($this->getCapital() !== null) {
            $benefits = $benefits->increase($this->getCapital());
        }

        return $benefits;
    }

    public function getPercentageBenefits(): ?float
    {
        if ($this->getBuy()->getValue() ==  0) {
            return 100;
        }

        $percentage = $this->getBenefits()->getValue() * 100 / $this->getBuy()->getValue();

        return $percentage;
    }

    public function getChange(): ?Money
    {
        $change = $this->getStock()->getChange();
        if ($change === null) {
            return null;
        }

        return $change->multiply($this->getAmount());
    }

    public function getPreClose(): ?Money
    {
        $preClose = $this->getStock()->getPreClose();
        if ($preClose === null) {
            return null;
        }

        return $preClose->multiply($this->getAmount());
    }

    public function getCapital(): ?Money
    {
        return $this->capital;
    }

    public function getPercentageCapital(): ?float
    {
        $priceBenefits = $this->getSell()
            ->decrease($this->getBuy())
        ;

        if ($this->getCapital() !== null) {
            $priceBenefits = $priceBenefits->increase($this->getCapital());
        }

        if ($this->getInvested()->getValue() == 0) {
            return 100;
        }

        $percentage = $priceBenefits->getValue() * 100 / $this->getInvested()->getValue();

        return $percentage;
    }

    public function setCapital(?Money $capital): void
    {
        $this->capital = $capital;
    }

    public function getWeightedAvgPrice(): ?Money
    {
        return $this->invested ? $this->invested->divide($this->getAmount()) : null;
    }

    public function getMetadata(): ?PositionMetadata
    {
        return $this->metadata;
    }

    public function setMetadata(?PositionMetadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
