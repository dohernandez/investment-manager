<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\PositionClosed;
use App\Domain\Wallet\Event\PositionDecreased;
use App\Domain\Wallet\Event\PositionDividendCredited;
use App\Domain\Wallet\Event\PositionIncreased;
use App\Domain\Wallet\Event\PositionOpened;
use App\Domain\Wallet\Event\PositionSplitReversed;
use App\Infrastructure\Date\Date;
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
     * @var Money
     */
    private $invested;

    public function getInvested(): Money
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

    public function getTitle(): string
    {
        return sprintf(
            '%s %s:%s - %d [%s]',
            $this->getWallet()->getName(),
            $this->getStock()->getMarket()->getSymbol(),
            $this->getStock()->getSymbol(),
            $this->getAmount(),
            $this->getBook()->getBenefits()
        );
    }

    public function getChange(): ?Money
    {
        $change = $this->getStock()->getChange();
        if ($change === null) {
            return null;
        }

        return $change->multiply($this->getAmount());
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
        $capital = $this->capital->increase($operation->getCapital());
        $buys = $this->book->getBuys()->increase($totalPaid);
        $benefits = $this->book->getSells()
            ->increase($this->book->getTotalDividendPaid())
            ->decrease($buys)
            ->increase($capital);

        $percentageBenefits = 100;
        if ($buys->getValue() > 0) {
            $percentageBenefits = $benefits->getValue() * 100 / $buys->getValue();
        }

        $invested = $this->invested->increase($totalPaid);
        $averagePrice = $invested->divide($amount);

        // set the owning side
        $this->operations->add($operation);
        $operation->setPosition($this);

        $stock = $operation->getStock();
        // this will keep stock sync in projection.
        $this->stock = $stock;

        $nextDividend = $stock->getNextDividend() ? $stock->getNextDividend()->multiply($amount) : null;
        $nextDividendYield = null;
        if ($nextDividend) {
            $nextDividendYield = $nextDividend->getValue() * 4 / \max($averagePrice->getValue(), 1) * 100;
        }

        $changed = $this->getStock()->getChange();
        $percentageChanged = 100;
        if ($changed !== null) {
            $changed = $changed->multiply($amount);

            if ($stock->getPreClose() !== null) {
                $percentageChanged = $changed->getValue() * 100 / $stock->getPreClose()->getValue();
            }
        }

        $preClosed = $stock->getPreClose();
        if ($preClosed !== null) {
            $preClosed = $preClosed->multiply($amount);
        }

        $this->recordChange(
            new PositionIncreased(
                $this->getId(),
                $amount,
                $invested,
                $capital,
                $averagePrice,
                $buys,
                $benefits,
                $percentageBenefits,
                $changed,
                $percentageChanged,
                $preClosed,
                $nextDividend,
                $nextDividendYield
            )
        );

        return $this;
    }

    public function decreasePosition(Operation $operation): self
    {
        $book = $this->book;

        $totalEarned = $operation->getTotalEarned();
        $amount = $this->getAmount() - $operation->getAmount();
        $sells = $this->book->getSells()->increase($totalEarned);
        $benefits = $sells
            ->increase($this->book->getTotalDividendPaid())
            ->decrease($book->getBuys());
        // This covers the case where the stock is received by split/reverse at cost zero.
        $percentageBenefits = 100;
        if ($book->getBuys()->getValue() > 0) {
            $percentageBenefits = $benefits->getValue() * 100 / $book->getBuys()->getValue();
        }

        // set the owning side
        $this->operations->add($operation);
        $operation->setPosition($this);

        if ($amount === 0) {
            $this->recordChange(
                new PositionClosed(
                    $this->getId(),
                    $operation->getDateAt(),
                    $sells,
                    $benefits,
                    $percentageBenefits
                )
            );

            return $this;
        }

        $invested = $this->invested->decrease($totalEarned);
        $averagePrice = $invested->divide($amount);
        if ($invested->getValue() < 0) {
            $invested = new Money($book->getCurrency());
            $averagePrice = new Money($book->getCurrency());
        }
        $capital = $this->capital->decrease($operation->getCapital());
        $benefits = $benefits->increase($capital);
        // This covers the case where the stock is received by split/reverse at cost zero.
        $percentageBenefits = 100;
        if ($book->getBuys()->getValue() > 0) {
            $percentageBenefits = $benefits->getValue() * 100 / $book->getBuys()->getValue();
        }

        $this->recordChange(
            new PositionDecreased(
                $this->getId(),
                $amount,
                $invested,
                $capital,
                $averagePrice,
                $sells,
                $benefits,
                $percentageBenefits
            )
        );

        return $this;
    }

    public function increaseDividend(Operation $operation): self
    {
        // set the owning side
        $this->operations->add($operation);
        $operation->setPosition($this);

        $book = $this->book;

        $bookDividendPaid = BookEntry::createBookEntry('dividends');

        $year = (string)Date::getYear($operation->getDateAt());
        $bookDividendPaidYearEntry = BookEntry::createYearEntry($bookDividendPaid, $year);
        $bookDividendPaid->getEntries()->add($bookDividendPaidYearEntry);

        $month = (string)Date::getMonth($operation->getDateAt());
        $bookDividendPaidMonthEntry = BookEntry::createMonthEntry($bookDividendPaidYearEntry, $month);
        $bookDividendPaidYearEntry->getEntries()->add($bookDividendPaidMonthEntry);

        if ($dividendPaid = $book->getDividendPaid()) {
            $bookDividendPaid->setTotal($dividendPaid->getTotal());

            if ($entry = $dividendPaid->getBookEntry($year)) {
                $bookDividendPaidYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $bookDividendPaidMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $bookDividendPaid->increaseTotal($operation->getValue());
        $bookDividendPaidYearEntry->increaseTotal($operation->getValue());
        $bookDividendPaidMonthEntry->increaseTotal($operation->getValue());

        $benefits = $book->getSells()
            ->increase($bookDividendPaid->getTotal())
            ->decrease($book->getBuys())
            ->increase($this->capital);
        // This covers the case the stock is received by split/reverse at cost zero.
        $percentageBenefits = 100;
        if ($book->getBuys()->getValue() > 0) {
            $percentageBenefits = $benefits->getValue() * 100 / $book->getBuys()->getValue();
        }

        $this->recordChange(
            new PositionDividendCredited(
                $this->getId(),
                $benefits,
                $percentageBenefits,
                $bookDividendPaid
            )
        );

        return $this;
    }

    public function splitReversePosition(Operation $operation): self
    {
        // set the owning side
        $this->operations->add($operation);
        $operation->setPosition($this);

        $book = $this->book;

        $amount = $operation->getAmount();
        $averagePrice = $this->invested->divide($amount);
        $capital = $operation->getCapital();

        $benefits = $book->getSells()
            ->increase($this->book->getTotalDividendPaid())
            ->decrease($book->getBuys())
            ->increase($capital);
        // This covers the case the stock is received at cost zero.
        $percentageBenefits = 100;
        if ($book->getBuys()->getValue() !== 0 ) {
            $percentageBenefits = $benefits->getValue() * 100 / $book->getBuys()->getValue();
        }

        $stock = $operation->getStock();
        // this will keep stock sync in projection.
        $this->stock = $stock;

        $nextDividend = $stock->getNextDividend() ? $stock->getNextDividend()->multiply($amount) : null;
        $nextDividendYield = null;
        if ($nextDividend) {
            $nextDividendYield = $nextDividend->getValue() * 4 / \max($averagePrice->getValue(), 1) * 100;
        }

        $changed = $this->getStock()->getChange();
        $percentageChanged = 100;
        if ($changed !== null) {
            $changed = $changed->multiply($amount);

            if ($stock->getPreClose() !== null) {
                $percentageChanged = $changed->getValue() * 100 / $stock->getPreClose()->getValue();
            }
        }

        $preClosed = $stock->getPreClose();
        if ($preClosed !== null) {
            $preClosed = $preClosed->multiply($amount);
        }

        $this->recordChange(
            new PositionSplitReversed(
                $this->id,
                $amount,
                $averagePrice,
                $capital,
                $benefits,
                $percentageBenefits,
                $changed,
                $percentageChanged,
                $preClosed,
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
                $this->book->setBuys($event->getBuys());
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());
                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());
                $this->book->setChanged($event->getChanged());
                $this->book->setPercentageChanged($event->getPercentageChanged());
                $this->book->setPreClosed($event->getPreClosed());

                break;

            case PositionDecreased::class:
                /** @var PositionDecreased $event */

                $this->amount = $event->getAmount();
                $this->invested = $event->getInvested();
                $this->capital = $event->getCapital();

                $this->book->setAveragePrice($event->getAveragePrice());
                $this->book->setSells($event->getSells());
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());

                break;

            case PositionClosed::class:
                /** @var PositionClosed $event */

                $this->status = self::STATUS_CLOSE;
                $this->closedAt = $event->getDateAt();
                $this->amount = 0;
                $this->invested = new Money($this->book->getCurrency());
                $this->capital = new Money($this->book->getCurrency());

                $this->book->setAveragePrice(new Money($this->book->getCurrency()));
                $this->book->setSells($event->getSells());
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());
                $this->book->setNextDividend(null);
                $this->book->setNextDividendYield(null);
                $this->book->setChanged(null);
                $this->book->setPercentageChanged(null);
                $this->book->setPreClosed(null);

                break;

            case PositionDividendCredited::class:
                /** @var PositionDividendCredited $event */

                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());

                $dividendPaid = $this->book->getDividendPaid();
                if (!$dividendPaid) {
                    $dividendPaid = $event->getDividendPaid();
                } else {
                    $dividendPaid->merge($event->getDividendPaid());
                }
                $this->book->setDividendPaid($dividendPaid);

                break;

            case PositionSplitReversed::class:
                /** @var PositionSplitReversed $event */

                $this->amount = $event->getAmount();
                $this->capital = $event->getCapital();
                $this->book->setAveragePrice($event->getAveragePrice());
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());
                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());
                $this->book->setChanged($event->getChanged());
                $this->book->setPercentageChanged($event->getPercentageChanged());
                $this->book->setPreClosed($event->getPreClosed());

                break;
        }
    }
}
