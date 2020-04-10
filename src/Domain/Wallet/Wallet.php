<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\WalletBuyOperationUpdated;
use App\Domain\Wallet\Event\WalletBuySellOperationUpdated;
use App\Domain\Wallet\Event\WalletCapitalUpdated;
use App\Domain\Wallet\Event\WalletConnectivityUpdated;
use App\Domain\Wallet\Event\WalletCreated;
use App\Domain\Wallet\Event\WalletDividendProjectedUpdated;
use App\Domain\Wallet\Event\WalletDividendsUpdated;
use App\Domain\Wallet\Event\WalletInterestUpdated;
use App\Domain\Wallet\Event\WalletInvestmentDecreased;
use App\Domain\Wallet\Event\WalletInvestmentIncreased;
use App\Domain\Wallet\Event\WalletInvestmentIncreasedDecreased;
use App\Domain\Wallet\Event\WalletSellOperationUpdated;
use App\Domain\Wallet\Event\WalletYearDividendProjectionCalculated;
use App\Infrastructure\Context\Context;
use App\Infrastructure\Date\Date;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\AggregateRootTypeTrait;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

use function count;

class Wallet extends AggregateRoot implements EventSourcedAggregateRoot
{
    use AggregateRootTypeTrait;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->positions = new ArrayCollection();
        $this->operations = new ArrayCollection();
    }

    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $slug;

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @var Broker
     */
    private $broker;

    public function getBroker(): Broker
    {
        return $this->broker;
    }

    /**
     * @var Account
     */
    private $account;

    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * This field is only use for find the wallet by the account id
     *
     * @var string
     */
    private $accountId;

    /**
     * @var WalletBook|null
     */
    private $book;

    public function getBook(): ?WalletBook
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

    public function getTitle(): string
    {
        return \sprintf('%s (%s)', $this->name, $this->slug);
    }

    /**
     * @var ArrayCollection|Position[]
     */
    private $positions;

    public function getPositions()
    {
        return $this->positions;
    }

    public function setPositions($positions): self
    {
        $this->positions = $positions;

        return $this;
    }

    /**
     * @var ArrayCollection|Operation[]
     */
    private $operations;

    public function getOperations()
    {
        return $this->operations;
    }

    public function getCurrency(): Currency
    {
        return $this->book->getCurrency();
    }

    public static function create(string $name, Broker $broker, Account $account, ?string $slug = null)
    {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $book = WalletBook::createWithInitialBalance($broker->getCurrency(), $account->getBalance());

        $self->recordChange(
            new WalletCreated(
                $id,
                $name,
                $broker,
                $account,
                $book,
                $slug
            )
        );

        return $self;
    }

    public function increaseInvestment(Money $invested)
    {
        $book = $this->getBook();

        $this->recordChange(
            new WalletInvestmentIncreased(
                $this->id,
                $book->getInvested()->increase($invested),
                $book->getFunds()->increase($invested)
            )
        );

        return $this;
    }

    public function decreaseInvestment(Money $invested)
    {
        $book = $this->getBook();

        $this->recordChange(
            new WalletInvestmentDecreased(
                $this->id,
                $book->getInvested()->decrease($invested),
                $book->getFunds()->decrease($invested)
            )
        );

        return $this;
    }

    public function updateBuyOperation(Operation $operation): self
    {
        $book = $this->book;

        $capital = $book->getCapital()->increase($operation->getCapital());
        $funds = $book->getFunds()->decrease($operation->getTotalPaid());
        $benefits = $this->calculateBenefits($capital, $funds, $book->getInvested());
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $book->getInvested());

        $bookCommissions = BookEntry::copyBookAtDateAndIncreasedValue(
            $operation->getDateAt(),
            $operation->getCommissionsPaid(),
            $book->getCommissions(),
            'commissions'
        );

        $this->recordChange(
            new WalletBuyOperationUpdated(
                $this->id,
                $operation->getDateAt(),
                $capital,
                $funds,
                $benefits,
                $percentageBenefits,
                $bookCommissions
            )
        );

        return $this;
    }

    public function updateSellOperation(Operation $operation): self
    {
        $book = $this->book;

        $capital = $book->getCapital()->decrease($operation->getCapital());
        $funds = $book->getFunds()->increase($operation->getTotalEarned());
        $benefits = $this->calculateBenefits($capital, $funds, $book->getInvested());
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $book->getInvested());

        $bookCommissions = BookEntry::copyBookAtDateAndIncreasedValue(
            $operation->getDateAt(),
            $operation->getCommissionsPaid(),
            $book->getCommissions(),
            'commissions'
        );

        $this->recordChange(
            new WalletSellOperationUpdated(
                $this->id,
                $operation->getDateAt(),
                $capital,
                $funds,
                $benefits,
                $percentageBenefits,
                $bookCommissions
            )
        );

        return $this;
    }

    public function increaseConnectivity(Operation $operation): self
    {
        $book = $this->book;

        $funds = $book->getFunds()->decrease($operation->getValue());
        $benefits = $this->calculateBenefits($book->getCapital(), $funds, $book->getInvested());
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $book->getInvested());

        $bookConnectivity = BookEntry::copyBookAtDateAndIncreasedValue(
            $operation->getDateAt(),
            $operation->getValue(),
            $book->getConnection(),
            'connectivity'
        );

        $this->recordChange(
            new WalletConnectivityUpdated(
                $this->id,
                $operation->getDateAt(),
                $funds,
                $benefits,
                $percentageBenefits,
                $bookConnectivity
            )
        );

        return $this;
    }

    public function increaseInterest(Operation $operation): self
    {
        $book = $this->book;

        $funds = $book->getFunds()->decrease($operation->getValue());
        $benefits = $this->calculateBenefits($book->getCapital(), $funds, $book->getInvested());
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $book->getInvested());

        $bookInterests = BookEntry::copyBookAtDateAndIncreasedValue(
            $operation->getDateAt(),
            $operation->getValue(),
            $book->getInterest(),
            'interests'
        );

        $this->recordChange(
            new WalletInterestUpdated(
                $this->id,
                $operation->getDateAt(),
                $funds,
                $benefits,
                $percentageBenefits,
                $bookInterests
            )
        );

        return $this;
    }

    public function updateDividendOperation(Operation $operation): self
    {
        $book = $this->book;

        $funds = $book->getFunds()->increase($operation->getValue());
        $benefits = $this->calculateBenefits($book->getCapital(), $funds, $book->getInvested());
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $book->getInvested());

        $bookDividends = BookEntry::copyBookAtDateAndIncreasedValue(
            $operation->getDateAt(),
            $operation->getValue(),
            $book->getDividends(),
            'dividends'
        );

        $this->recordChange(
            new WalletDividendsUpdated(
                $this->id,
                $operation->getDateAt(),
                $funds,
                $benefits,
                $percentageBenefits,
                $bookDividends
            )
        );

        return $this;
    }

    public function increaseCapital(Money $capital, $toUpdateAt = 'now'): self
    {
        $capital = $this->book->getCapital()->increase($capital);

        return $this->updateCapital($capital, $toUpdateAt);
    }

    private function updateCapital(Money $capital, $toUpdateAt = 'now'): self
    {
        $toUpdateAt = new DateTime($toUpdateAt);

        $book = $this->book;

        $benefits = $this->calculateBenefits($capital, $book->getFunds(), $book->getInvested());
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $book->getInvested());

        if ($changed = $this->findIfLastChangeHappenedIsName(WalletCapitalUpdated::class)) {
            // This is to avoid have too much update events.

            $this->replaceChangedPayload(
                $changed,
                new WalletCapitalUpdated(
                    $this->id,
                    $capital,
                    $benefits,
                    $percentageBenefits,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new WalletCapitalUpdated(
                $this->id,
                $capital,
                $benefits,
                $percentageBenefits,
                $toUpdateAt
            )
        );

        return $this;
    }

    public function forceSetCapital(Money $capital, $toUpdateAt = 'now'): self
    {
        return $this->updateCapital($capital, $toUpdateAt);
    }

    private function calculateBenefits(Money $capital, Money $funds, Money $invested): Money
    {
        return $capital->increase($funds)->decrease($invested);
    }

    private function calculatePercentageBenefits(Money $benefits, Money $invested): float
    {
        // This covers the case the stock is received by split/reverse at cost zero.
        if ($invested->getValue() > 0) {
            return $benefits->getValue() * 100 / $invested->getValue();
        }

        return 100;
    }

    /**
     * @param int $year
     * @param Rate[]|null $exchangeMoneyRates
     * @param string $toUpdateAt
     *
     * @return $this
     */
    public function calculateYearDividendProjected(
        int $year,
        array $exchangeMoneyRates = null,
        $toUpdateAt = 'now'
    ): self {
        $toUpdateAt = new DateTime($toUpdateAt);

        $book = BookEntry::createBookEntry('dividends_projection');
        // book entry year
        $bookYearEntry = BookEntry::createYearEntry($book, $year);
        $book->getEntries()->add($bookYearEntry);
        $bookYearEntry->setTotal(new Money($this->getCurrency()));

        $this->calculateDividendProjected(Context::TODO(), $bookYearEntry, $year, 1, $exchangeMoneyRates);

        if ($changed = $this->findIfLastChangeHappenedIsName(WalletYearDividendProjectionCalculated::class)) {
            // This is to avoid have too much update events.

            $this->replaceChangedPayload(
                $changed,
                new WalletYearDividendProjectionCalculated(
                    $this->id,
                    $book,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new WalletYearDividendProjectionCalculated(
                $this->id,
                $book,
                $toUpdateAt
            )
        );

        return $this;
    }

    private function calculateDividendProjected(
        Context $context,
        BookEntry $bookYearEntry,
        int $year,
        int $fromMonth = 1,
        array $exchangeMoneyRates = null
    ) {
        $context = $context->addKeysAndValues(
            [
                'func'      => 'Wallet.calculateDividendProjected',
                'year'      => $year,
                'fromMonth' => $fromMonth,
                'positions' => count($this->positions),
            ]
        );

        foreach ($this->positions as $i => $position) {
            $stock = $position->getStock();

            $context = $context->addKeysAndValues(
                [
                    'i'     => $i,
                    'stock' => (string)$stock,
                ]
            );

            $dividends = $stock->getDividends();
            if (!$dividends || $dividends->isEmpty()) {
                Logger::debug(
                    $context->addKeysAndValues(
                        [
                            'dividends' => $dividends,
                        ]
                    ),
                    'skip position, does not pay dividend'
                );

                continue;
            }

            // dividends year
            $dividends = $dividends->filter(
                function (StockDividend $dividend) use ($year, $fromMonth) {
                    $exDate = $dividend->getExDate();

                    return Date::getYear($exDate) === $year && Date::getMonth($exDate) >= $fromMonth;
                }
            );

            $exchangeMoneyRate = null;

            if (!empty($exchangeMoneyRates) && !$stock->getCurrency()->equals($this->getCurrency())) {
                foreach ($exchangeMoneyRates as $rate) {
                    if ($rate->getFromCurrency()->equals($stock->getCurrency())) {
                        $exchangeMoneyRate = $rate;

                        break;
                    }
                }
            }

            $amount = $position->getAmount();
            // TODO get retention based on dividend exDate for more accuracy projection
            $totalDividendRetentionExchanged = $totalDividendRetention = $position->getBook(
            )->getTotalDividendRetention() ?
                $position->getBook()->getTotalDividendRetention()->multiply($amount) :
                null;

            if ($exchangeMoneyRate) {
                $totalDividendRetentionExchanged = $exchangeMoneyRate->exchange($totalDividendRetention);
            }

            $context = $context->addKeysAndValues(
                [
                    'dividends'                       => count($dividends),
                    'exchangeMoneyRate'               => (string)$exchangeMoneyRate,
                    'amount'                          => $amount,
                    'totalDividendRetention'          => (string)$totalDividendRetention,
                    'totalDividendRetentionExchanged' => (string)$totalDividendRetentionExchanged,
                ]
            );

            /** @var StockDividend $dividend */
            foreach ($dividends as $j => $dividend) {
                // book entry month
                $month = (string)Date::getMonth($dividend->getExDate());
                $bookMonthEntry = $bookYearEntry->getBookEntry($month);
                if (!$bookMonthEntry) {
                    $bookMonthEntry = BookEntry::createMonthEntry($bookYearEntry, $month);
                    $bookMonthEntry->setTotal(new Money($this->getCurrency()));
                    $bookMonthEntry->setMetadata(new BookEntryMetadata());
                    $bookYearEntry->getEntries()->add($bookMonthEntry);
                }

                $totalValueExchanged = $totalValue = $dividend->getValue()->multiply($amount);
                if ($exchangeMoneyRate) {
                    $totalValueExchanged = $exchangeMoneyRate->exchange($totalValue);
                }

                $context = $context->addKeysAndValues(
                    [
                        'j'                   => $j,
                        'exDate'              => $dividend->getExDate()->format(Date::FORMAT_SPANISH),
                        'month'               => $month,
                        'bookMonthEntryTotal' => (string)$bookMonthEntry->getTotal(),
                    ]
                );
                Logger::debug(
                    $context->addKeysAndValues(
                        [
                            'totalValue'          => (string)$totalValue,
                            'totalValueExchanged' => (string)$totalValueExchanged,
                        ]
                    ),
                    'before calculate dividend projected'
                );

                $totalValue = $totalValue->decrease($totalDividendRetention);
                $totalValueExchanged = $totalValueExchanged->decrease($totalDividendRetentionExchanged);

                Logger::debug(
                    $context->addKeysAndValues(
                        [
                            'totalValue'          => (string)$totalValue,
                            'totalValueExchanged' => (string)$totalValueExchanged,
                        ]
                    ),
                    'after calculate dividend projected'
                );

                $bookMonthEntry->setTotal($bookMonthEntry->getTotal()->increase($totalValueExchanged));

                $bookMonthEntryMetadata = $bookMonthEntry->getMetadata();
                $exchangeMonthTicket = $bookMonthEntryMetadata->getExchangeTicket($stock->getCurrency());
                $bookMonthEntryMetadata->setExchangeTicket(
                    $stock->getCurrency(),
                    new ExchangeTicket(
                        $exchangeMoneyRate,
                        $exchangeMonthTicket ?
                            $exchangeMonthTicket->getMoneyOriginalCurrency()->increase($totalValue) :
                            $totalValue,
                        $exchangeMonthTicket ?
                            $exchangeMonthTicket->getMoney()->increase($totalValueExchanged) :
                            $totalValueExchanged
                    )
                );

                $context = $context->addKeysAndValues(
                    [
                        'bookMonthEntryTotal' => (string)$bookMonthEntry->getTotal(),
                    ]
                );
                Logger::debug($context, 'book month entry updated');

                $bookYearEntry->setTotal($bookYearEntry->getTotal()->increase($totalValueExchanged));

                Logger::debug(
                    $context->addKeysAndValues(
                        [
                            'bookYearEntryTotal' => (string)$bookYearEntry->getTotal(),
                        ]
                    ),
                    'book year entry updated'
                );
            }
        }
    }

    /**
     * @param int $year
     * @param Rate[] $exchangeMoneyRates
     * @param string $toUpdateAt
     *
     * @return $this
     */
    public function updateDividendProjected(array $exchangeMoneyRates, $toUpdateAt = 'now'): self
    {
        $toUpdateAt = new DateTime($toUpdateAt);

        $bookDividendsProjected = BookEntry::copyBookFromWindow(
            $this->book->getDividendsProjected(),
            (clone $toUpdateAt)->add(new DateInterval('P1M'))
        );

        foreach ($bookDividendsProjected->getEntries() as $bookYearEntry) {
            $yearTotal = $bookYearEntry->getTotal();

            foreach ($bookYearEntry->getEntries() as $bookMonthEntry) {
                $yearTotal = $yearTotal->decrease($bookMonthEntry->getTotal());
                $bookMonthEntryMetadata = $bookMonthEntry->getMetadata();

                foreach ($exchangeMoneyRates as $rate) {
                    $toCurrency = $rate->getToCurrency();

                    if ($bookMonthTicket = $bookMonthEntryMetadata->getExchangeTicket($toCurrency)) {
                        $bookMonthEntryMetadata->setExchangeTicket(
                            $toCurrency,
                            new ExchangeTicket(
                                $rate,
                                $bookMonthTicket->getMoneyOriginalCurrency(),
                                $rate->exchange($bookMonthTicket->getMoneyOriginalCurrency())
                            )
                        );
                    }
                }
                $bookMonthEntry->reCalculateTotalFromMetadataTickets();

                $yearTotal = $yearTotal->increase($bookMonthEntry->getTotal());
            }

            $bookYearEntry->setTotal($yearTotal);
        }

        if ($changed = $this->findIfLastChangeHappenedIsName(WalletYearDividendProjectionCalculated::class)) {
            // This is to avoid have too much update events.

            $this->replaceChangedPayload(
                $changed,
                new WalletDividendProjectedUpdated(
                    $this->id,
                    $bookDividendsProjected,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new WalletDividendProjectedUpdated(
                $this->id,
                $bookDividendsProjected,
                $toUpdateAt
            )
        );

        return $this;
    }

    /**
     * @param Context $context
     * @param DateTime $date
     * @param Rate[]|null $exchangeMoneyRates
     * @param string $toUpdateAt
     *
     * @return $this
     */
    public function reCalculateDividendProjectedFromDate(
        Context $context,
        DateTime $date,
        array $exchangeMoneyRates = null,
        $toUpdateAt = 'now'
    ): self {
        $context = $context->addKeysAndValues(
            [
                'func' => 'Wallet.reCalculateDividendProjectedFromDate',
                'date' => $date->format(Date::FORMAT_SPANISH),
            ]
        );

        $toUpdateAt = new DateTime($toUpdateAt);

        $year = Date::getYear($date);
        $beggingYear = Date::getDateTimeBeginYear($year);

        $context = $context->addKeysAndValues(
            [
                'year'        => $year,
                'beggingYear' => $beggingYear->format(Date::FORMAT_SPANISH),
            ]
        );

        $book = BookEntry::copyBookFromWindow(
            $this->book->getDividendsProjected(),
            $beggingYear,
            $date
        );

        Logger::debug(
            $context->addKeysAndValues(
                [
                    'copiedBook' => $book,
                ]
            ),
            'created copy book from window'
        );

        // book entry year
        $context = $context->addKeysAndValues(
            [
                'bookName' => $book->getName(),
                'currency' => (string)$this->getCurrency(),
            ]
        );

        $bookRecalculated = BookEntry::createBookEntry($book->getName());
        // book entry year
        $bookRecalculatedYearEntry = BookEntry::createYearEntry($bookRecalculated, $year);
        $bookRecalculated->getEntries()->add($bookRecalculatedYearEntry);
        $bookRecalculatedYearEntry->setTotal(new Money($this->getCurrency()));

        Logger::debug(
            $context->addKeysAndValues(
                [
                    'bookRecalculated' => $bookRecalculated,
                ]
            ),
            'created book recalculated'
        );

        $fromDate = (clone $date)->add(new DateInterval('P1M'));
        if (Date::getYear($fromDate) != $year) {
            return $this;
        }

        $context = $context->addKeysAndValues(
            [
                'fromDate' => $fromDate->format('c'),
            ]
        );

        $this->calculateDividendProjected(
            $context,
            $bookRecalculatedYearEntry,
            $year,
            Date::getMonth($fromDate),
            $exchangeMoneyRates
        );

        Logger::debug(
            $context->addKeysAndValues(
                [
                    'bookRecalculatedYearEntry' => [
                        'total' => (string)$bookRecalculatedYearEntry->getTotal(),
                        $year   => $bookRecalculatedYearEntry->getEntries()->map(function (BookEntry $bookEntry) {
                            return $bookEntry->getName() . ' - ' . (string)$bookEntry->getTotal();
                        })->toArray(),
                    ],
                ]
            ),
            'dividend projected calculated'
        );

        $bookYear = $book->getBookEntry($year);
        for ($month = 1; $month <= Date::getMonth($date); $month++) {
            $bookMonth = $bookYear->getBookEntry($month);

            if (!$bookMonth) {
                continue;
            }

            $bookRecalculatedYearEntry->setTotal(
                $bookRecalculatedYearEntry->getTotal()->increase($bookMonth->getTotal())
            );
        }

        Logger::debug(
            $context->addKeysAndValues(
                [
                    'bookRecalculatedYearEntry' => [
                        'total' => (string)$bookRecalculatedYearEntry->getTotal(),
                    ],
                ]
            ),
            'updated total dividend projected calculated'
        );

        if ($changed = $this->findIfLastChangeHappenedIsName(WalletYearDividendProjectionCalculated::class)) {
            // This is to avoid have too much update events.

            $this->replaceChangedPayload(
                $changed,
                new WalletYearDividendProjectionCalculated(
                    $this->id,
                    $bookRecalculated,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new WalletYearDividendProjectionCalculated(
                $this->id,
                $book,
                $toUpdateAt
            )
        );

        return $this;
    }

    protected function apply(Changed $changed)
    {
        $event = $changed->getPayload();

        $this->updatedAt = $changed->getCreatedAt();

        switch ($changed->getEventName()) {
            case WalletCreated::class:
                /** @var WalletCreated $event */

                $this->name = $event->getName();
                $this->slug = $event->getSlug();

                $this->broker = $event->getBroker();
                $this->account = $event->getAccount();
                $this->book = $event->getBook();
                $this->createdAt = $changed->getCreatedAt();

                $this->accountId = $this->account->getId();

                break;

            case WalletInvestmentIncreased::class:
            case WalletInvestmentDecreased::class:
                /** @var WalletInvestmentIncreasedDecreased $event */

                $this->book->setInvested($event->getInvested());
                $this->book->setFunds($event->getFunds());

                break;

            case WalletBuyOperationUpdated::class:
            case WalletSellOperationUpdated::class:
                /** @var WalletBuySellOperationUpdated $event */

                $this->book
                    ->setCapital($event->getCapital())
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $dividendsProjection = $this->book->getCommissions();
                if (!$dividendsProjection) {
                    $dividendsProjection = $event->getCommissions();
                } else {
                    $dividendsProjection->merge($event->getCommissions());
                }
                $this->book->setCommissions($dividendsProjection);

                break;

            case WalletConnectivityUpdated::class:
                /** @var WalletConnectivityUpdated $event */

                $this->book
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $dividendsProjection = $this->book->getConnection();
                if (!$dividendsProjection) {
                    $dividendsProjection = $event->getConnectivity();
                } else {
                    $dividendsProjection->merge($event->getConnectivity());
                }
                $this->book->setConnection($dividendsProjection);

                break;

            case WalletInterestUpdated::class:
                /** @var WalletInterestUpdated $event */

                $this->book
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $dividendsProjection = $this->book->getInterest();
                if (!$dividendsProjection) {
                    $dividendsProjection = $event->getInterests();
                } else {
                    $dividendsProjection->merge($event->getInterests());
                }
                $this->book->setInterest($dividendsProjection);

                break;

            case WalletDividendsUpdated::class:
                /** @var WalletDividendsUpdated $event */

                $this->book
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $dividendsProjection = $this->book->getDividends();
                if (!$dividendsProjection) {
                    $dividendsProjection = $event->getDividends();
                } else {
                    $dividendsProjection->merge($event->getDividends());
                }
                $this->book->setDividends($dividendsProjection);

                break;

            case WalletCapitalUpdated::class:
                /** @var WalletCapitalUpdated $event */

                $this->book
                    ->setCapital($event->getCapital())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                break;

            case WalletYearDividendProjectionCalculated::class:
                /** @var WalletYearDividendProjectionCalculated $event */

                $dividendsProjection = $this->book->getDividendsProjected();
                if (!$dividendsProjection) {
                    $dividendsProjection = $event->getYearDividendProjected();
                } else {
                    $dividendsProjection->merge($event->getYearDividendProjected());
                }
                $this->book->setDividendsProjection($dividendsProjection);

                break;

            case WalletDividendProjectedUpdated::class:
                /** @var WalletDividendProjectedUpdated $event */

                $dividendsProjection = $this->book->getDividendsProjected();
                if (!$dividendsProjection) {
                    $dividendsProjection = $event->getYearDividendProjected();
                } else {
                    $dividendsProjection->merge($event->getYearDividendProjected());
                }
                $this->book->setDividendsProjection($dividendsProjection);

                break;
        }
    }
}
