<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\BuyOperationRegistered;
use App\Domain\Wallet\Event\BuySellOperationRegistered;
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

    public const TYPES_EXCHANGEABLE = [
        self::TYPE_BUY,
        self::TYPE_SELL,
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

    public function setPosition(Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @var Wallet
     */
    private $wallet;

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getTotalPaid(): Money
    {
        return $this->value->increase($this->getCommissionsPaid());
    }

    public function getCommissionsPaid(): Money
    {
        if ($this->commission === null) {
            if ($this->priceChangeCommission === null) {
                return new Money($this->getWallet()->getCurrency());
            }

            return $this->priceChangeCommission;
        }

        return $this->commission->increase($this->priceChangeCommission);
    }

    public function getTotalEarned(): Money
    {
        return $this->value->decrease($this->getCommissionsPaid());
    }

    public function getCapital(): Money
    {
        if (!\in_array($this->type, Operation::TYPES_EXCHANGEABLE)) {
            return new Money($this->wallet->getCurrency());
        }

        $price = $this->getStock()->getPrice();

        if ($this->exchangeRate) {
            $price = $price->exchange(
                $this->exchangeRate->getToCurrency(),
                $this->exchangeRate->getRate(),
                $price->getPrecision()
            );
        }

        return $price->multiply($this->getAmount());
    }

    /**
     * @var ExchangeRate|null
     */
    private $exchangeRate;

    public function getExchangeRate(): ?ExchangeRate
    {
        return $this->exchangeRate;
    }

    public function getTitle(): string
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

        if ($this->getType() === self::TYPE_SPLIT_REVERSE) {
            return sprintf(
                '%s [%s]',
                $this->getType(),
                $this->getAmount()
            );
        }

        return sprintf(
            '%s %s:%s - %d [%s]',
            $this->getType(),
            $this->getStock()->getMarket()->getSymbol(),
            $this->getStock()->getSymbol(),
            $this->getAmount(),
            $this->getNetValue()
        );
    }

    public function getNetValue(): Money
    {
        if ($this->getType() === self::TYPE_BUY) {
            return $this->getTotalPaid();
        }

        if ($this->getType() === self::TYPE_SELL) {
            return $this->getTotalEarned();
        }

        return $this->getValue();
    }

    public static function register(
        Wallet $wallet,
        DateTime $dateAt,
        string $type,
        ?Money $value,
        ?Stock $stock = null,
        ?int $amount = null,
        ?Money $price = null,
        ?Money $priceChange = null,
        ?Money $priceChangeCommission = null,
        ?Money $commission = null,
        ?ExchangeRate $exchangeRate = null
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
                    $commission,
                    $exchangeRate
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
                    $value,
                    $exchangeRate
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

        $this->createdAt = $changed->getCreatedAt();
        $this->updatedAt = $changed->getCreatedAt();

        switch ($changed->getEventName()) {
            case BuyOperationRegistered::class:
            case SellOperationRegistered::class:
                /** @var BuySellOperationRegistered $event */

                $this->value = $event->getValue();
                $this->stock = $event->getStock();
                $this->stockId = $this->stock->getId();
                $this->amount = $event->getAmount();
                $this->price = $event->getPrice();
                $this->priceChange = $event->getPriceChange();
                $this->priceChangeCommission = $event->getPriceChangeCommission();
                $this->commission = $event->getCommission();
                $this->exchangeRate = $event->getExchangeRate();

                break;

            case InterestOperationRegistered::class:
            case ConnectivityOperationRegistered::class:
                /** @var BuySellOperationRegistered $event */

                $this->value = $event->getValue();

                break;

            case DividendOperationRegistered::class:
                /** @var DividendOperationRegistered $event */

                $this->value = $event->getValue();
                $this->stock = $event->getStock();
                $this->stockId = $this->stock->getId();
                $this->price = $event->getPrice();
                $this->priceChange = $event->getPriceChange();
                $this->priceChangeCommission = $event->getPriceChangeCommission();

                break;

            case SplitReverseOperationRegistered::class:
                /** @var SplitReverseOperationRegistered $event */

                $this->stock = $event->getStock();
                $this->stockId = $this->stock->getId();
                $this->amount = $event->getAmount();
                $this->exchangeRate = $event->getExchangeRate();

                break;
        }
    }
}
