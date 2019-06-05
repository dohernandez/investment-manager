<?php

namespace App\Entity;

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
     * @ORM\JoinColumn(nullable=false)
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
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $price;

    /**
     * Price in dollar. TODO In the future it should be extracted using the money pattern.
     * @ORM\Column(type="decimal", precision=11, scale=4, nullable=true)
     */
    private $priceChange;

    /**
     * Price in euro. TODO In the future it should be extracted using the money pattern.
     * @ORM\Column(type="decimal", precision=11, scale=4, nullable=true)
     */
    private $priceChangeCommission;

    /**
     * Value of the operation in euro. TODO In the future it should be extracted using the money pattern.
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $value;

    /**
     * Price in euro. TODO In the future it should be extracted using the money pattern.
     * @ORM\Column(type="decimal", precision=11, scale=4, nullable=true)
     */
    private $commission;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trade", inversedBy="operations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $trade;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet", inversedBy="operations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wallet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Position", inversedBy="operations", cascade={"persist"})
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceChange(): ?float
    {
        return $this->priceChange;
    }

    public function setPriceChange(?float $priceChange): self
    {
        $this->priceChange = $priceChange;

        return $this;
    }

    public function getPriceChangeCommission(): ?float
    {
        return $this->priceChangeCommission;
    }

    public function setPriceChangeCommission(?float $priceChangeCommission): self
    {
        $this->priceChangeCommission = $priceChangeCommission;

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setCommission(?float $commission): self
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

    public function getTrade(): ?Trade
    {
        return $this->trade;
    }

    public function setTrade(?Trade $trade): self
    {
        $this->trade = $trade;

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

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s:%s - %d [%.2f] ',
            $this->getStock()->getMarket()->getSymbol(),
            $this->getStock()->getSymbol(),
            $this->getAmount(),
            $this->getCapital()
        );
    }

    public function getNetValue(): float
    {
        if ($this->getType() === self::TYPE_BUY) {
            return $this->getFinalCommissionPaid() + $this->getValue();
        }

        if ($this->getType() === self::TYPE_SELL) {
            return $this->getValue() - $this->getFinalCommissionPaid();
        }

        return $this->getValue();
    }

    public function getFinalCommissionPaid(): float
    {
        return $this->getCommission() + $this->getPriceChangeCommission();
    }

    public function getCapital(): float
    {
        $stock = $this->getStock();
        if ($stock === null) {
            return 0;
        }

        return $this->getAmount() * $stock->getValue();
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
