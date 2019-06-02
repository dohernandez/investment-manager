<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OperationRepository")
 */
class Operation
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Trade", inversedBy="operations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trade;

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
}
