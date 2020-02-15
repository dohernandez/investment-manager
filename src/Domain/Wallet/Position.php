<?php

namespace App\Domain\Wallet;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Position extends AggregateRoot implements EventSourcedAggregateRoot
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSE = 'close';

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
     * @var int
     */
    private $amount;

    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @var Money|null
     */
    private $invested;

    public function getInvested(): ?Money
    {
        return $this->invested;
    }

    /**
     * @var Money|null
     */
    private $buy;

    public function getBuy(): ?Money
    {
        return $this->buy;
    }

    /**
     * @var Money|null
     */
    private $sell;

    public function getSell(): ?Money
    {
        return $this->sell;
    }

    /**
     * @var string
     */
    private $status;

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @var DateTime
     */
    private $openedAt;

    public function getOpenedAt(): DateTime
    {
        return $this->openedAt;
    }

    /**
     * @var DateTime|null
     */
    private $closedAt;

    public function getClosedAt(): ?DateTime
    {
        return $this->closedAt;
    }

    /**
     * @var Money|null
     */
    private $capital;

    public function getCapital(): ?Money
    {
        return $this->capital;
    }

    /**
     * @var PositionBook
     */
    private $book;

    public function getBook(): PositionBook
    {
        return $this->book;
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
     * @var ArrayCollection|Operation[]
     */
    private $operations;

    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * @var Wallet
     */
    private $wallet;

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->operations = new ArrayCollection();
    }

    protected function apply(Changed $changed)
    {
        // TODO: Implement apply() method.
    }
}
