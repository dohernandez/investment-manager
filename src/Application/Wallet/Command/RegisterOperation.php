<?php

namespace App\Application\Wallet\Command;

use App\Domain\Wallet\Stock;
use App\Infrastructure\Money\Money;
use DateTime;

final class RegisterOperation
{
    /**
     * @var string
     */
    private $walletId;

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
     * @var Stock|null
     */
    private $stock;

    /**
     * @var int|null
     */
    private $amount;

    /**
     * @var Money|null
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
        string $walletId,
        DateTime $dateAt,
        string $type,
        Money $value,
        ?Stock $stock = null,
        ?int $amount = null,
        ?Money $price = null,
        ?Money $priceChange= null,
        ?Money $priceChangeCommission = null,
        ?Money $commission = null
    )
    {
        $this->walletId = $walletId;
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

    public function getWalletId(): string
    {
        return $this->walletId;
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

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getPrice(): ?Money
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
