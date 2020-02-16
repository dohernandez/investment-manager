<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\PositionIncreased;
use App\Domain\Wallet\Event\PositionOpened;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Position extends AggregateRoot implements EventSourcedAggregateRoot
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSE = 'close';

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->operations = new ArrayCollection();
    }

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
     * @var Money
     */
    private $capital;

    public function getCapital(): Money
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

    public static function open(Wallet $wallet, Stock $stock, DateTime $openedAt): self
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $book = PositionBook::create($wallet->getCurrency());

        $self->recordChange(
            new PositionOpened(
                $id,
                $wallet,
                $stock,
                $openedAt,
                $book
            )
        );

        return $self;
    }

    public function increasePosition(Operation $operation): self
    {
        $totalPaid = $operation->getTotalPaid();

        $amount = $this->getAmount() + $operation->getAmount();
        $invested = $this->invested->increase($totalPaid);
        $capital = $this->capital->increase($operation->getCapital());

        // set the owning side
        $this->operations->add($operation);
        $operation->setPosition($this);

        $stock = $operation->getStock();
        // this will keep stock sync in projection.
        $this->stock = $stock;

        $averagePrice = $invested->divide($amount);

        $buy = $this->book->getBuy()->increase($totalPaid);
        $benefits = $this->book->getSell()
            ->increase($this->book->getTotalDividendPaid())
            ->decrease($buy);

        $percentageBenefits = $benefits->getValue() * 100 / $buy->getValue();

        $nextDividend = $stock->getNextDividend() ? $stock->getNextDividend()->multiply($amount) : null;
        $nextDividendYield = null;
        if ($nextDividend) {
            $nextDividendYield = $nextDividend->getValue() * 4 / \max($averagePrice->getValue(), 1) * 100;
        }

        $this->recordChange(
            new PositionIncreased(
                $this->getId(),
                $amount,
                $invested,
                $capital,
                $averagePrice,
                $buy,
                $benefits,
                $percentageBenefits,
                $nextDividend,
                $nextDividendYield
            )
        );

        return $this;
    }

    protected function apply(Changed $changed)
    {
        $this->updatedAt = $changed->getCreatedAt();

        $event = $changed->getPayload();

        switch ($changed->getEventName()) {
            case PositionOpened::class:
                /** @var PositionOpened $event */

                $this->wallet = $event->getWallet();
                $this->stock = $event->getStock();
                $this->stockId = $this->stock->getId();
                $this->openedAt = $event->getOpenedAt();

                $this->status = self::STATUS_OPEN;
                $this->amount = 0;
                $this->invested = new Money($this->wallet->getCurrency());
                $this->capital = new Money($this->wallet->getCurrency());

                $this->book = $event->getBook();

                $this->createdAt = $changed->getCreatedAt();

                break;

            case PositionIncreased::class:
                /** @var PositionIncreased $event */

                $this->amount = $event->getAmount();
                $this->invested = $event->getInvested();
                $this->capital = $event->getCapital();

                $this->book->setAveragePrice($event->getAveragePrice());
                $this->book->setBuy($event->getBuy());
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());
                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());

                break;
        }
    }
}
