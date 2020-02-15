<?php

namespace App\Domain\Wallet;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use DateTime;

class Operation extends AggregateRoot implements EventSourcedAggregateRoot
{
    public const TYPE_BUY = 'buy';
    public const TYPE_SELL = 'sell';
    public const TYPE_CONNECTIVITY = 'connectivity';
    public const TYPE_DIVIDEND = 'dividend';
    public const TYPE_INTEREST = 'interest';
    public const TYPE_SPLIT_REVERSE = 'split/reverse';

    public const TYPES = [
        self::TYPE_BUY,
        self::TYPE_SELL,
        self::TYPE_CONNECTIVITY,
        self::TYPE_DIVIDEND,
        self::TYPE_INTEREST,
        self::TYPE_SPLIT_REVERSE,
    ];

    /**
     * @var Stock
     */
    private $stock;

    public function getStock(): Stock
    {
        return $this->stock;
    }

    /**
     * This field is only use for find the operation by the stock id
     *
     * @var string
     */
    private $stockId;

    /**
     * @var DateTime
     */
    private $dateAt;

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }

    /**
     * @var string
     */
    private $type;

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @var Money
     */
    private $price;

    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @var Money
     */
    private $priceChange;

    public function getPriceChange(): Money
    {
        return $this->priceChange;
    }

    /**
     * @var Money
     */
    private $priceChangeCommission;

    public function getPriceChangeCommission(): Money
    {
        return $this->priceChangeCommission;
    }

    /**
     * @var Money
     */
    private $value;

    public function getValue(): Money
    {
        return $this->value;
    }

    /**
     * @var Money
     */
    private $commission;

    public function getCommission(): Money
    {
        return $this->commission;
    }

    /**
     * @var int
     */
    private $amount;

    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @var DateTime
     */
    private $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @var DateTime
     */
    private $updatedAt;

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @var Position
     */
    private $position;

    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @var Wallet
     */
    private $wallet;

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    protected function apply(Changed $changed)
    {
        // TODO: Implement apply() method.
    }
}
