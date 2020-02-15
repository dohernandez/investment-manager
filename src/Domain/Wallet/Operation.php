<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\BuyOperationRegistered;
use App\Domain\Wallet\Event\ConnectivityOperationRegistered;
use App\Domain\Wallet\Event\DividendOperationRegistered;
use App\Domain\Wallet\Event\InterestOperationRegistered;
use App\Domain\Wallet\Event\SellOperationRegistered;
use App\Domain\Wallet\Event\SplitReverseOperationRegistered;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
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
     * @var Stock|null
     */
    private $stock;

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    /**
     * This field is only use for find the operation by the stock id
     *
     * @var string|null
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
     * @var Money|null
     */
    private $price;

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    /**
     * @var Money|null
     */
    private $priceChange;

    public function getPriceChange(): ?Money
    {
        return $this->priceChange;
    }

    /**
     * @var Money|null
     */
    private $priceChangeCommission;

    public function getPriceChangeCommission(): ?Money
    {
        return $this->priceChangeCommission;
    }

    /**
     * @var Money|null
     */
    private $value;

    public function getValue(): ?Money
    {
        return $this->value;
    }

    /**
     * @var Money|null
     */
    private $commission;

    public function getCommission(): ?Money
    {
        return $this->commission;
    }

    /**
     * @var int|null
     */
    private $amount;

    public function getAmount(): ?int
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

    public static function register(
        Wallet $wallet,
        DateTime $dateAt,
        string $type,
        Money $value,
        ?Stock $stock = null,
        ?int $amount = null,
        ?Money $price = null,
        ?Money $priceChange = null,
        ?Money $priceChangeCommission = null,
        ?Money $commission = null
    ): self {
        $id = UUID\Generator::generate();

        $self = new static($id);

        switch ($type) {
            case Operation::TYPE_BUY:
            case Operation::TYPE_SELL:
                $eventClass = BuyOperationRegistered::class;

                if ($type === self::TYPE_SELL) {
                    $eventClass = SellOperationRegistered::class;
                }

                $event = new $eventClass(
                    $id,
                    $wallet,
                    $dateAt,
                    $type,
                    $value,
                    $stock,
                    $amount,
                    $price,
                    $priceChange,
                    $priceChangeCommission,
                    $commission
                );

                break;

            case Operation::TYPE_INTEREST:
            case Operation::TYPE_CONNECTIVITY:
                $eventClass = InterestOperationRegistered::class;

                if ($type === self::TYPE_CONNECTIVITY) {
                    $eventClass = ConnectivityOperationRegistered::class;
                }

                $event = new $eventClass(
                    $id,
                    $wallet,
                    $dateAt,
                    $type,
                    $value
                );

                break;

            case Operation::TYPE_DIVIDEND:
                $event = new DividendOperationRegistered(
                    $id,
                    $wallet,
                    $dateAt,
                    $type,
                    $value,
                    $stock,
                    $price,
                    $priceChange,
                    $priceChangeCommission
                );

                break;

            case Operation::TYPE_SPLIT_REVERSE:
                $event = new SplitReverseOperationRegistered(
                    $id,
                    $wallet,
                    $dateAt,
                    $type,
                    $stock,
                    $amount,
                    $value
                );

                break;

            default:
                throw new \InvalidArgumentException(\sprintf('Invalid type "%s"', $type));
        }

        $self->recordChange($event);

        return $self;
    }

    protected function apply(Changed $changed)
    {
        $event = $changed->getPayload();

        $this->wallet = $event->getWallet();
        $this->dateAt = $event->getDateAt();
        $this->type = $event->getType();

        switch ($changed->getEventName()) {
            case BuyOperationRegistered::class:
            case SellOperationRegistered::class:
                $this->value = $event->getValue();
                $this->stock = $event->getStock();
                $this->stockId = $this->stock->getId();
                $this->amount = $event->getAmount();
                $this->price = $event->getPrice();
                $this->priceChange = $event->getPriceChange();
                $this->priceChangeCommission = $event->getPriceChangeCommission();
                $this->commission = $event->getCommission();

                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case InterestOperationRegistered::class:
            case ConnectivityOperationRegistered::class:

                $this->value = $event->getValue();

                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case DividendOperationRegistered::class:
                /** @var DividendOperationRegistered $event */

                $this->value = $event->getValue();
                $this->stock = $event->getStock();
                $this->stockId = $this->stock->getId();
                $this->price = $event->getPrice();
                $this->priceChange = $event->getPriceChange();
                $this->priceChangeCommission = $event->getPriceChangeCommission();

                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;

            case SplitReverseOperationRegistered::class:
                /** @var SplitReverseOperationRegistered $event */

                $this->stock = $event->getStock();
                $this->amount = $event->getAmount();
                $this->value = $event->getValue();

                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;
        }
    }
}
