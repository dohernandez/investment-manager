<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Stock;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Money\Money;
use DateTime;

final class SplitReverseOperationRegistered
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
     * @var int
     */
    private $value;

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var Stock
     */
    private $stock;

    public function __construct(
        string $id,
        Wallet $wallet,
        DateTime $dateAt,
        string $type,
        Stock $stock,
        int $amount,
        ?Money $value = null
    )
    {
        $this->wallet = $wallet;
        $this->dateAt = $dateAt;
        $this->type = $type;
        $this->value = $value;
        $this->id = $id;
        $this->amount = $amount;
        $this->stock = $stock;
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

    public function getValue(): ?Money
    {
        return $this->value;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }
}
