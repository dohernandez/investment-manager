<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\PositionClosed;
use App\Domain\Wallet\Event\PositionDecreased;
use App\Domain\Wallet\Event\PositionDividendCredited;
use App\Domain\Wallet\Event\PositionDividendRetentionUpdated;
use App\Domain\Wallet\Event\PositionIncreased;
use App\Domain\Wallet\Event\PositionOpened;
use App\Domain\Wallet\Event\PositionSplitReversed;
use App\Domain\Wallet\Event\PositionStockDividendUpdated;
use App\Domain\Wallet\Event\PositionStockPriceUpdated;
use App\Infrastructure\Date\Date;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

use function max;

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
        $book = $this->book;

        $totalPaid = $operation->getTotalPaid();
        $amount = $this->getAmount() + $operation->getAmount();
        $capital = $this->capital->increase($operation->getCapital());
        $buys = $book->getBuys()->increase($totalPaid);
        $benefits = $this->calculateBenefits(
            $book->getSells(),
            $book->getTotalDividendPaid(),
            $buys,
            $capital
        );

        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $buys);
        $invested = $this->invested->increase($totalPaid);
        $averagePrice = $invested->divide($amount);

        // set the owning side
        $this->operations->add($operation);
        $operation->setPosition($this);

        $stock = $operation->getStock();
        $exchangeMoneyRate = $operation->getExchangeMoneyRate();

        $nextDividend = $stock->getNextDividend();
        $nextDividendYield = null;
        $nextDividendAfterTaxes = null;
        $nextDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $nextDividend,
            $stock->getNextYearDividend(),
            $nextDividendYield,
            $nextDividendAfterTaxes,
            $nextDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        $changed = $stock->getChange();
        $preClosed = $stock->getPreClose();
        $percentageChanged = null;
        $this->bindClosePrice(
            $changed,
            $preClosed,
            $percentageChanged,
            $exchangeMoneyRate
        );

        $this->recordChange(
            new PositionIncreased(
                $this->getId(),
                $stock,
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
                $nextDividendYield,
                $nextDividendAfterTaxes,
                $nextDividendYieldAfterTaxes
            )
        );

        return $this;
    }

    private function calculateBenefits(Money $sells, Money $dividendPaid, Money $buys, ?Money $capital = null): Money
    {
        if (!$capital) {
            $capital = new Money($this->book->getCurrency());
        }

        return $sells
            ->increase($dividendPaid)
            ->decrease($buys)
            ->increase($capital);
    }

    private function binDividendValue(
        ?Money &$dividend,
        ?Money $yearDividend,
        ?float &$dividendYield,
        ?Money &$dividendAfterTaxes,
        ?float &$dividendYieldAfterTaxes,
        ?Rate $exchangeMoneyRate = null
    ): self {
        $amount = $this->amount;
        $book = $this->book;
        $averagePrice = $book->getAveragePrice();

        $dividend = $dividend ? $dividend->multiply($amount) : null;
        $yearDividend = $yearDividend ? $yearDividend->multiply($amount) : null;
        $totalDividendRetention = $this->book->getTotalDividendRetention() ?
            $this->book->getTotalDividendRetention()->multiply($amount) :
            null;

        // exchange capital, change and pre close to the position currency
        if ($exchangeMoneyRate) {
            $dividend = $exchangeMoneyRate->exchange($dividend);
            $yearDividend = $exchangeMoneyRate->exchange($yearDividend);
            $totalDividendRetention = $exchangeMoneyRate->exchange($totalDividendRetention);
        }

        $dividendYield = null;
        if ($yearDividend) {
            $dividendYield = $yearDividend->getValue() / max(
                    $averagePrice->multiply($amount)->getValue(),
                    1
                ) * 100;

            if ($dividendYield > 100) {
                $dividendYield = 100;
            }
        }

        $dividendAfterTaxes = null;
        $dividendYieldAfterTaxes = null;
        if ($dividend) {
            $yearDividendAfterTaxes = null;

            if (!$totalDividendRetention) {
                $dividendAfterTaxes = $dividend;
                $yearDividendAfterTaxes = $yearDividend;
            } else {
                $dividendAfterTaxes = $dividend->decrease($totalDividendRetention);
                $yearDividendAfterTaxes = $yearDividend->decrease($totalDividendRetention);
            }

            if ($yearDividendAfterTaxes) {
                $dividendYieldAfterTaxes = $yearDividendAfterTaxes->getValue() / max(
                        $averagePrice->multiply($amount)->getValue(),
                        1
                    ) * 100;

                if ($dividendYieldAfterTaxes > 100) {
                    $dividendYieldAfterTaxes = 100;
                }
            }
        }

        return $this;
    }

    private function bindClosePrice(
        ?Money &$change,
        ?Money &$preClose,
        ?float &$percentageChange,
        ?Rate $exchangeMoneyRate = null
    ): self {
        $amount = $this->amount;

        $change = $change ? $change->multiply($amount) : null;
        $preClose = $preClose ? $preClose->multiply($amount) : null;

        // exchange capital, change and pre close to the position currency
        if ($exchangeMoneyRate) {
            $change = $exchangeMoneyRate->exchange($change);
            $preClose = $exchangeMoneyRate->exchange($preClose);
        }

        $percentageChange = null;
        if ($change !== null && $preClose !== null) {
            $percentageChange = $change->getValue() * 100 / $preClose->getValue();
        }

        return $this;
    }

    private function calculatePercentageBenefits(Money $benefits, Money $buys): float
    {
        // This covers the case the stock is received by split/reverse at cost zero.
        if ($buys->getValue() > 0) {
            return $benefits->getValue() * 100 / $buys->getValue();
        }

        return 100;
    }

    public function decreasePosition(Operation $operation): self
    {
        $book = $this->book;

        $totalEarned = $operation->getTotalEarned();
        $amount = $this->getAmount() - $operation->getAmount();
        $sells = $book->getSells()->increase($totalEarned);

        // set the owning side
        $this->operations->add($operation);
        $operation->setPosition($this);

        if ($amount === 0) {
            $buys = $book->getBuys();
            $benefits = $this->calculateBenefits($sells, $book->getTotalDividendPaid(), $buys);
            $percentageBenefits = $this->calculatePercentageBenefits($benefits, $buys);

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
        $buys = $book->getBuys();
        $benefits = $this->calculateBenefits($sells, $book->getTotalDividendPaid(), $buys, $capital);
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $buys);

        $stock = $operation->getStock();
        $exchangeMoneyRate = $operation->getExchangeMoneyRate();

        $nextDividend = $stock->getNextDividend();
        $nextDividendYield = null;
        $nextDividendAfterTaxes = null;
        $nextDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $nextDividend,
            $stock->getNextYearDividend(),
            $nextDividendYield,
            $nextDividendAfterTaxes,
            $nextDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        $this->recordChange(
            new PositionDecreased(
                $this->getId(),
                $amount,
                $invested,
                $capital,
                $averagePrice,
                $sells,
                $benefits,
                $percentageBenefits,
                $nextDividend,
                $nextDividendYield,
                $nextDividendAfterTaxes,
                $nextDividendYieldAfterTaxes
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

        $bookDividendPaid = $this->getCopyBookForDateWithIncreasedValue(
            $operation->getDateAt(),
            $operation->getValue(),
            $book->getDividendPaid(),
            'dividends'
        );

        $buys = $book->getBuys();
        $benefits = $this->calculateBenefits(
            $book->getSells(),
            $bookDividendPaid->getTotal(),
            $buys,
            $this->capital
        );
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $buys);

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

    private function getCopyBookForDateWithIncreasedValue(
        DateTime $date,
        Money $value,
        ?BookEntry $book,
        ?string $copyBookName = null
    ): BookEntry {
        $copyBookName = $copyBookName ? $copyBookName : $book ? $book->getName() : null;
        if (!$copyBookName) {
            throw new InvalidArgumentException('Copy book name can not be empty');
        }

        $copyBook = BookEntry::createBookEntry($copyBookName);

        // book entry year
        $year = (string)Date::getYear($date);
        $copyBookYearEntry = BookEntry::createYearEntry($book, $year);
        $copyBook->getEntries()->add($copyBookYearEntry);

        // book entry month
        $month = (string)Date::getMonth($date);
        $copyBookMonthEntry = BookEntry::createMonthEntry($copyBookYearEntry, $month);
        $copyBookYearEntry->getEntries()->add($copyBookMonthEntry);

        // set current total, year and month value
        if ($book) {
            $copyBook->setTotal($book->getTotal());

            if ($entry = $book->getBookEntry($year)) {
                $copyBookYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $copyBookMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $copyBook->increaseTotal($value);
        $copyBookYearEntry->increaseTotal($value);
        $copyBookMonthEntry->increaseTotal($value);

        return $copyBook;
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

        $buys = $book->getBuys();
        $benefits = $this->calculateBenefits(
            $book->getSells(),
            $book->getTotalDividendPaid(),
            $buys,
            $capital
        );
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $buys);

        $stock = $operation->getStock();
        $exchangeMoneyRate = $operation->getExchangeMoneyRate();

        $nextDividend = $stock->getNextDividend();
        $nextDividendYield = null;
        $nextDividendAfterTaxes = null;
        $nextDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $nextDividend,
            $stock->getNextYearDividend(),
            $nextDividendYield,
            $nextDividendAfterTaxes,
            $nextDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        $changed = $stock->getChange();
        $preClosed = $stock->getPreClose();
        $percentageChanged = null;
        $this->bindClosePrice(
            $changed,
            $preClosed,
            $percentageChanged,
            $exchangeMoneyRate
        );

        $this->recordChange(
            new PositionSplitReversed(
                $this->id,
                $stock,
                $amount,
                $averagePrice,
                $capital,
                $benefits,
                $percentageBenefits,
                $changed,
                $percentageChanged,
                $preClosed,
                $nextDividend,
                $nextDividendYield,
                $nextDividendAfterTaxes,
                $nextDividendYieldAfterTaxes
            )
        );

        return $this;
    }

    public function updateStockPrice(Stock $stock, ?Rate $exchangeMoneyRate = null, $toUpdateAt = 'now'): self
    {
        $toUpdateAt = new DateTime($toUpdateAt);

        $capital = $stock->getPrice()->multiply($this->amount);

        // exchange capital, changed and pre close to the position currency
        if ($exchangeMoneyRate) {
            $capital = $exchangeMoneyRate->exchange($capital);
        }

        $book = $this->book;
        $buys = $book->getBuys();

        $benefits = $this->calculateBenefits(
            $book->getSells(),
            $book->getTotalDividendPaid(),
            $buys,
            $capital
        );
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $buys);

        $changed = $stock->getChange();
        $preClosed = $stock->getPreClose();
        $percentageChanged = null;
        $this->bindClosePrice(
            $changed,
            $preClosed,
            $percentageChanged,
            $exchangeMoneyRate
        );

        $nextDividend = $stock->getNextDividend();
        $nextDividendYield = null;
        $nextDividendAfterTaxes = null;
        $nextDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $nextDividend,
            $stock->getNextYearDividend(),
            $nextDividendYield,
            $nextDividendAfterTaxes,
            $nextDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        if ($change = $this->findFirstChangeHappenedDateAt($toUpdateAt, PositionStockPriceUpdated::class)) {
            // This is to avoid have too much update events.

            $this->replaceChangedPayload(
                $change,
                new PositionStockPriceUpdated(
                    $this->id,
                    $stock,
                    $capital,
                    $benefits,
                    $percentageBenefits,
                    $changed,
                    $percentageChanged,
                    $preClosed,
                    $nextDividend,
                    $nextDividendYield,
                    $nextDividendAfterTaxes,
                    $nextDividendYieldAfterTaxes,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new PositionStockPriceUpdated(
                $this->id,
                $stock,
                $capital,
                $benefits,
                $percentageBenefits,
                $changed,
                $percentageChanged,
                $preClosed,
                $nextDividend,
                $nextDividendYield,
                $nextDividendAfterTaxes,
                $nextDividendYieldAfterTaxes,
                $toUpdateAt
            )
        );

        return $this;
    }

    public function updateDividendRetention(?Money $retention, ?Rate $exchangeMoneyRate, $toUpdateAt = 'now'): self
    {
        $toUpdateAt = new DateTime($toUpdateAt);

        $bookDividendRetention = $this->getCopyBookForDateWithIncreasedValue(
            $toUpdateAt,
            $retention,
            null,
            'dividends'
        );

        $stock = $this->stock;

        $nextDividend = $stock->getNextDividend();
        $nextDividendYield = null;
        $nextDividendAfterTaxes = null;
        $nextDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $nextDividend,
            $stock->getNextYearDividend(),
            $nextDividendYield,
            $nextDividendAfterTaxes,
            $nextDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        $toPayDividend = $stock->getToPayDividend();
        $toPayDividendYield = null;
        $toPayDividendAfterTaxes = null;
        $toPayDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $toPayDividend,
            $stock->getNextYearDividend(),
            $toPayDividendYield,
            $toPayDividendAfterTaxes,
            $toPayDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        if ($changed = $this->findFirstChangeHappenedDateAt($toUpdateAt, PositionDividendRetentionUpdated::class)) {
            // This is to avoid have too much update events.

            $this->replaceChangedPayload(
                $changed,
                new PositionDividendRetentionUpdated(
                    $this->id,
                    $nextDividendAfterTaxes,
                    $nextDividendYieldAfterTaxes,
                    $toPayDividendAfterTaxes,
                    $toPayDividendYieldAfterTaxes,
                    $bookDividendRetention,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new PositionDividendRetentionUpdated(
                $this->id,
                $nextDividendAfterTaxes,
                $nextDividendYieldAfterTaxes,
                $toPayDividendAfterTaxes,
                $toPayDividendYieldAfterTaxes,
                $bookDividendRetention,
                $toUpdateAt
            )
        );

        return $this;
    }

    public function updateStockDividend(Stock $stock, ?Rate $exchangeMoneyRate = null, $toUpdateAt = 'now'): self
    {
        $toUpdateAt = new DateTime($toUpdateAt);

        $nextDividend = $stock->getNextDividend();
        $nextDividendYield = null;
        $nextDividendAfterTaxes = null;
        $nextDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $nextDividend,
            $stock->getNextYearDividend(),
            $nextDividendYield,
            $nextDividendAfterTaxes,
            $nextDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        $toPayDividend = $stock->getToPayDividend();
        $toPayDividendYield = null;
        $toPayDividendAfterTaxes = null;
        $toPayDividendYieldAfterTaxes = null;
        $this->binDividendValue(
            $toPayDividend,
            $stock->getNextYearDividend(),
            $toPayDividendYield,
            $toPayDividendAfterTaxes,
            $toPayDividendYieldAfterTaxes,
            $exchangeMoneyRate
        );

        if ($changed = $this->findFirstChangeHappenedDateAt($toUpdateAt, PositionStockDividendUpdated::class)) {
            // This is to avoid have too much update events.

            $this->replaceChangedPayload(
                $changed,
                new PositionStockDividendUpdated(
                    $this->id,
                    $stock,
                    $nextDividend,
                    $nextDividendYield,
                    $nextDividendAfterTaxes,
                    $nextDividendYieldAfterTaxes,
                    $toPayDividend,
                    $toPayDividendYield,
                    $toPayDividendAfterTaxes,
                    $toPayDividendYieldAfterTaxes,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new PositionStockDividendUpdated(
                $this->id,
                $stock,
                $nextDividend,
                $nextDividendYield,
                $nextDividendAfterTaxes,
                $nextDividendYieldAfterTaxes,
                $toPayDividend,
                $toPayDividendYield,
                $toPayDividendAfterTaxes,
                $toPayDividendYieldAfterTaxes,
                $toUpdateAt
            )
        );

        return $this;
    }

    /**
     * @param Rate $exchangeMoneyRate
     * @param string $toUpdateAt
     *
     * @return self
     */
    public function updateMoneyRate(Rate $exchangeMoneyRate, $toUpdateAt = 'now'): self
    {
        return $this->updateStockPrice($this->stock, $exchangeMoneyRate, $toUpdateAt)
            ->updateStockDividend($this->stock, $exchangeMoneyRate, $toUpdateAt);
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
                /** @var PositionIncreased $event
                 */

                $this->stock = $event->getStock();

                $this->amount = $event->getAmount();
                $this->invested = $event->getInvested();
                $this->capital = $event->getCapital();

                $this->book->setAveragePrice($event->getAveragePrice());
                $this->book->setBuys($event->getBuys());
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());
                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());
                $this->book->setNextDividendAfterTaxes($event->getNextDividendAfterTaxes());
                $this->book->setNextDividendYieldAfterTaxes($event->getNextDividendYieldAfterTaxes());
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
                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());
                $this->book->setNextDividendAfterTaxes($event->getNextDividendAfterTaxes());
                $this->book->setNextDividendYieldAfterTaxes($event->getNextDividendYieldAfterTaxes());

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
                $this->book->setNextDividendAfterTaxes(null);
                $this->book->setNextDividendYieldAfterTaxes(null);
                $this->book->setToPayDividend(null);
                $this->book->setToPayDividendYield(null);
                $this->book->setToPayDividendAfterTaxes(null);
                $this->book->setToPayDividendYieldAfterTaxes(null);
                $this->book->setChanged(null);
                $this->book->setPercentageChanged(null);
                $this->book->setPreClosed(null);

                break;

            case PositionDividendCredited::class:
                /** @var PositionDividendCredited $event */

                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());

                $dividendRetention = $this->book->getDividendPaid();
                if (!$dividendRetention) {
                    $dividendRetention = $event->getDividendPaid();
                } else {
                    $dividendRetention->merge($event->getDividendPaid());
                }
                $this->book->setDividendPaid($dividendRetention);

                break;

            case PositionSplitReversed::class:
                /** @var PositionSplitReversed $event */

                $this->stock = $event->getStock();

                $this->amount = $event->getAmount();
                $this->capital = $event->getCapital();
                $this->book->setAveragePrice($event->getAveragePrice());
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());
                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());
                $this->book->setNextDividendAfterTaxes($event->getNextDividendAfterTaxes());
                $this->book->setNextDividendYieldAfterTaxes($event->getNextDividendYieldAfterTaxes());
                $this->book->setChanged($event->getChanged());
                $this->book->setPercentageChanged($event->getPercentageChanged());
                $this->book->setPreClosed($event->getPreClosed());

                break;

            case PositionStockPriceUpdated::class:
                /** @var PositionStockPriceUpdated $event */

                $this->stock = $event->getStock();

                $this->capital = $event->getCapital();
                $this->book->setBenefits($event->getBenefits());
                $this->book->setPercentageBenefits($event->getPercentageBenefits());
                $this->book->setChanged($event->getChange());
                $this->book->setPercentageChanged($event->getPercentageChange());
                $this->book->setPreClosed($event->getPreClose());
                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());
                $this->book->setNextDividendAfterTaxes($event->getNextDividendAfterTaxes());
                $this->book->setNextDividendYieldAfterTaxes($event->getNextDividendYieldAfterTaxes());

                break;

            case PositionDividendRetentionUpdated::class:
                /** @var PositionDividendRetentionUpdated $event */

                $this->book->setNextDividendAfterTaxes($event->getNextDividendAfterTaxes());
                $this->book->setNextDividendYieldAfterTaxes($event->getNextDividendYieldAfterTaxes());
                $this->book->setToPayDividendAfterTaxes($event->getToPayDividendAfterTaxes());
                $this->book->setToPayDividendYieldAfterTaxes($event->getToPayDividendYieldAfterTaxes());

                $dividendRetention = $this->book->getDividendRetention();
                if (!$dividendRetention) {
                    $dividendRetention = $event->getDividendRetention();
                } else {
                    $dividendRetention->merge($event->getDividendRetention());
                }
                $this->book->setDividendRetention($dividendRetention);

                break;

            case PositionStockDividendUpdated::class:
                /** @var PositionStockDividendUpdated $event */

                $this->stock = $event->getStock();

                $this->book->setNextDividend($event->getNextDividend());
                $this->book->setNextDividendYield($event->getNextDividendYield());
                $this->book->setNextDividendAfterTaxes($event->getNextDividendAfterTaxes());
                $this->book->setNextDividendYieldAfterTaxes($event->getNextDividendYieldAfterTaxes());
                $this->book->setToPayDividend($event->getToPayDividend());
                $this->book->setToPayDividendYield($event->getToPayDividendYield());
                $this->book->setToPayDividendAfterTaxes($event->getToPayDividendAfterTaxes());
                $this->book->setToPayDividendYieldAfterTaxes($event->getToPayDividendYieldAfterTaxes());

                break;
        }
    }
}
