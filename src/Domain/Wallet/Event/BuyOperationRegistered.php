<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Stock;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Money\Money;
use DateTime;

final class BuyOperationRegistered
{
    /**
     * @var Wallet
     */
    private $wallet;

    /**
     * @var DateTime
     */
    private $dateAt;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Money
     */
    private $value;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var Money
     */
    private $price;

    /**
     * @var Money|null
     */
    private $priceChange;

    /**
     * @var Money|null
     */
    private $priceChangeCommission;

    /**
     * @var Money|null
     */
    private $commission;

    public function __construct(
        string $id,
        Wallet $wallet,
        DateTime $dateAt,
        string $type,
        Money $value,
        Stock $stock,
        int $amount,
        Money $price,
        ?Money $priceChange= null,
        ?Money $priceChangeCommission = null,
        ?Money $commission = null
    )
    {
        $this->id = $id;
        $this->wallet = $wallet;
        $this->dateAt = $dateAt;
        $this->type = $type;
        $this->value = $value;
        $this->stock = $stock;
        $this->amount = $amount;
        $this->price = $price;
        $this->priceChange = $priceChange;
        $this->priceChangeCommission = $priceChangeCommission;
        $this->commission = $commission;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): Money
    {
        return $this->value;
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getPriceChange(): ?Money
    {
        return $this->priceChange;
    }

    public function getPriceChangeCommission(): ?Money
    {
        return $this->priceChangeCommission;
    }

    public function getCommission(): ?Money
    {
        return $this->commission;
    }
}
