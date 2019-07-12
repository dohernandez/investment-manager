<?php

namespace App\Entity;

use App\VO\Money;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OperationRepository")
 */
class Operation implements Entity
{
    const TYPE_BUY = 'buy';
    const TYPE_SELL = 'sell';
    const TYPE_CONNECTIVITY = 'connectivity';
    const TYPE_DIVIDEND = 'dividend';
    const TYPE_INTEREST = 'interest';

    const TYPES = [
        self::TYPE_BUY,
        self::TYPE_SELL,
        self::TYPE_CONNECTIVITY,
        self::TYPE_DIVIDEND,
        self::TYPE_INTEREST,
    ];

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stock", inversedBy="operations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAt;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $type;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $price;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $priceChange;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $priceChangeCommission;

    /**
     * @var Money
     *
     * @ORM\Column(type="money")
     */
    private $value;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $commission;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="operations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wallet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Position", inversedBy="operations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $position;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $capital;

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

    public function getDateAt(): ?\DateTimeInterface
    {
        return $this->dateAt;
    }

    public function setDateAt(\DateTimeInterface $dateAt): self
    {
        $this->dateAt = $dateAt;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function setPrice(?Money $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceChange(): ?Money
    {
        return $this->priceChange;
    }

    public function setPriceChange(?Money $priceChange): self
    {
        $this->priceChange = $priceChange;

        return $this;
    }

    public function getPriceChangeCommission(): ?Money
    {
        return $this->priceChangeCommission;
    }

    public function setPriceChangeCommission(?Money $priceChangeCommission): self
    {
        $this->priceChangeCommission = $priceChangeCommission;

        return $this;
    }

    public function getValue(): ?Money
    {
        return $this->value;
    }

    public function setValue(Money $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCommission(): ?Money
    {
        return $this->commission;
    }

    public function setCommission(?Money $commission): self
    {
        $this->commission = $commission;

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

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): self
    {
        if ($this->wallet !== $wallet) {

            $this->wallet = $wallet;

            // Updating dividend of new stock
            if ($this->wallet) {
                $this->wallet->addOperation($this);
            }
        }

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        if (in_array($this->getType(), [
            self::TYPE_CONNECTIVITY,
            self::TYPE_INTEREST
        ])) {
            return sprintf(
                '%s [%s]',
                $this->getType(),
                $this->getNetValue()
            );
        }

        return sprintf(
            '%s %s:%s - %d [%s]',
            $this->getType(),
            $this->getStock()->getMarket()->getSymbol(),
            $this->getStock()->getSymbol(),
            $this->getAmount(),
            $this->getCapital()
        );
    }

    public function getNetValue(): Money
    {
        if ($this->getType() === self::TYPE_BUY) {
            return $this->getFinalCommissionPaid()->increase($this->getValue());
        }

        if ($this->getType() === self::TYPE_SELL) {
            return $this->getValue()->decrease($this->getFinalCommissionPaid());
        }

        return $this->getValue();
    }

    public function getFinalCommissionPaid(): Money
    {
        return $this->getCommission()->increase($this->getPriceChangeCommission());
    }

    public function getCapital(): ?Money
    {
        return $this->capital;
    }

    public function setCapital(?Money $capital): self
    {
        $this->capital = $capital;

        return $this;
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
}
