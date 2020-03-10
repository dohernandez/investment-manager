<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\WalletBuyOperationUpdated;
use App\Domain\Wallet\Event\WalletBuySellOperationUpdated;
use App\Domain\Wallet\Event\WalletConnectivityUpdated;
use App\Domain\Wallet\Event\WalletCreated;
use App\Domain\Wallet\Event\WalletDividendsUpdated;
use App\Domain\Wallet\Event\WalletInterestUpdated;
use App\Domain\Wallet\Event\WalletInvestmentDecreased;
use App\Domain\Wallet\Event\WalletInvestmentIncreased;
use App\Domain\Wallet\Event\WalletInvestmentIncreasedDecreased;
use App\Domain\Wallet\Event\WalletSellOperationUpdated;
use App\Domain\Wallet\Event\WalletCapitalUpdated;
use App\Infrastructure\Date\Date;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Wallet extends AggregateRoot implements EventSourcedAggregateRoot
{
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
     * @var WalletBook
     */
    private $book;

    public function getBook(): WalletBook
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
        $benefits = $capital->increase($funds->decrease($book->getInvested()));

        $percentageBenefits = $book->getInvested()->getValue() ?
            $benefits->getValue() * 100 / $book->getInvested()->getValue() :
            100;

        $bookCommissions = BookEntry::createBookEntry('commissions');

        $year = (string)Date::getYear($operation->getDateAt());
        $bookCommissionsYearEntry = BookEntry::createYearEntry($bookCommissions, $year);
        $bookCommissions->getEntries()->add($bookCommissionsYearEntry);

        $month = (string)Date::getMonth($operation->getDateAt());
        $bookCommissionsMonthEntry = BookEntry::createMonthEntry($bookCommissionsYearEntry, $month);
        $bookCommissionsYearEntry->getEntries()->add($bookCommissionsMonthEntry);

        if ($commissions = $book->getCommissions()) {
            $bookCommissions->setTotal($commissions->getTotal());

            if ($entry = $commissions->getBookEntry($year)) {
                $bookCommissionsYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $bookCommissionsMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $bookCommissions->increaseTotal($operation->getCommissionsPaid());
        $bookCommissionsYearEntry->increaseTotal($operation->getCommissionsPaid());
        $bookCommissionsMonthEntry->increaseTotal($operation->getCommissionsPaid());

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
        $benefits = $capital->increase($funds->decrease($book->getInvested()));

        $percentageBenefits = $book->getInvested()->getValue() ?
            $benefits->getValue() * 100 / $book->getInvested()->getValue() :
            100;

        $bookCommissions = BookEntry::createBookEntry('commissions');

        $year = (string)Date::getYear($operation->getDateAt());
        $bookCommissionsYearEntry = BookEntry::createYearEntry($bookCommissions, $year);
        $bookCommissions->getEntries()->add($bookCommissionsYearEntry);

        $month = (string)Date::getMonth($operation->getDateAt());
        $bookCommissionsMonthEntry = BookEntry::createMonthEntry($bookCommissionsYearEntry, $month);
        $bookCommissionsYearEntry->getEntries()->add($bookCommissionsMonthEntry);

        if ($commissions = $book->getCommissions()) {
            $bookCommissions->setTotal($commissions->getTotal());

            if ($entry = $commissions->getBookEntry($year)) {
                $bookCommissionsYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $bookCommissionsMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $bookCommissions->increaseTotal($operation->getCommissionsPaid());
        $bookCommissionsYearEntry->increaseTotal($operation->getCommissionsPaid());
        $bookCommissionsMonthEntry->increaseTotal($operation->getCommissionsPaid());

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
        $benefits = $book->getCapital()->increase($funds->decrease($book->getInvested()));

        $percentageBenefits = $book->getInvested()->getValue() ?
            $benefits->getValue() * 100 / $book->getInvested()->getValue() :
            100;

        $bookConnectivity = BookEntry::createBookEntry('connectivity');

        $year = (string)Date::getYear($operation->getDateAt());
        $bookConnectivityYearEntry = BookEntry::createYearEntry($bookConnectivity, $year);
        $bookConnectivity->getEntries()->add($bookConnectivityYearEntry);

        $month = (string)Date::getMonth($operation->getDateAt());
        $bookConnectivityMonthEntry = BookEntry::createMonthEntry($bookConnectivityYearEntry, $month);
        $bookConnectivityYearEntry->getEntries()->add($bookConnectivityMonthEntry);

        if ($connectivity = $book->getConnection()) {
            $bookConnectivity->setTotal($connectivity->getTotal());

            if ($entry = $connectivity->getBookEntry($year)) {
                $bookConnectivityYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $bookConnectivityMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $bookConnectivity->increaseTotal($operation->getValue());
        $bookConnectivityYearEntry->increaseTotal($operation->getValue());
        $bookConnectivityMonthEntry->increaseTotal($operation->getValue());

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
        $benefits = $book->getCapital()->increase($funds->decrease($book->getInvested()));

        $percentageBenefits = $book->getInvested()->getValue() ?
            $benefits->getValue() * 100 / $book->getInvested()->getValue() :
            100;

        $bookInterests = BookEntry::createBookEntry('interests');

        $year = (string)Date::getYear($operation->getDateAt());
        $bookInterestsYearEntry = BookEntry::createYearEntry($bookInterests, $year);
        $bookInterests->getEntries()->add($bookInterestsYearEntry);

        $month = (string)Date::getMonth($operation->getDateAt());
        $bookInterestsMonthEntry = BookEntry::createMonthEntry($bookInterestsYearEntry, $month);
        $bookInterestsYearEntry->getEntries()->add($bookInterestsMonthEntry);

        if ($interests = $book->getInterest()) {
            $bookInterests->setTotal($interests->getTotal());

            if ($entry = $interests->getBookEntry($year)) {
                $bookInterestsYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $bookInterestsMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $bookInterests->increaseTotal($operation->getValue());
        $bookInterestsYearEntry->increaseTotal($operation->getValue());
        $bookInterestsMonthEntry->increaseTotal($operation->getValue());

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
        $benefits = $book->getCapital()->increase($funds->decrease($book->getInvested()));

        $percentageBenefits = $book->getInvested()->getValue() ?
            $benefits->getValue() * 100 / $book->getInvested()->getValue() :
            100;

        $bookDividends = BookEntry::createBookEntry('dividends');

        $year = (string)Date::getYear($operation->getDateAt());
        $bookDividendsYearEntry = BookEntry::createYearEntry($bookDividends, $year);
        $bookDividends->getEntries()->add($bookDividendsYearEntry);

        $month = (string)Date::getMonth($operation->getDateAt());
        $bookDividendsMonthEntry = BookEntry::createMonthEntry($bookDividendsYearEntry, $month);
        $bookDividendsYearEntry->getEntries()->add($bookDividendsMonthEntry);

        if ($dividends = $book->getDividends()) {
            $bookDividends->setTotal($dividends->getTotal());

            if ($entry = $dividends->getBookEntry($year)) {
                $bookDividendsYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $bookDividendsMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $bookDividends->increaseTotal($operation->getValue());
        $bookDividendsYearEntry->increaseTotal($operation->getValue());
        $bookDividendsMonthEntry->increaseTotal($operation->getValue());

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
        $toUpdateAt = new DateTime($toUpdateAt);

        $book = $this->book;

        $capital = $book->getCapital()->increase($capital);
        $benefits = $capital->increase($book->getFunds()->decrease($book->getInvested()));

        $percentageBenefits = $book->getInvested()->getValue() ?
            $benefits->getValue() * 100 / $book->getInvested()->getValue() :
            100;

        if ($changed = $this->findFirstChangeHappenedDateAt($toUpdateAt, WalletCapitalUpdated::class)) {
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
        $toUpdateAt = new DateTime($toUpdateAt);

        $book = $this->book;

        $benefits = $this->calculateBenefits($capital, $book->getFunds(), $book->getInvested());
        $percentageBenefits = $this->calculatePercentageBenefits($benefits, $book->getInvested());

        if ($changed = $this->findFirstChangeHappenedDateAt($toUpdateAt, WalletCapitalUpdated::class)) {
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

                $dividends = $this->book->getCommissions();
                if (!$dividends) {
                    $dividends = $event->getCommissions();
                } else {
                    $dividends->merge($event->getCommissions());
                }
                $this->book->setCommissions($dividends);

                break;

            case WalletConnectivityUpdated::class:
                /** @var WalletConnectivityUpdated $event */

                $this->book
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $dividends = $this->book->getConnection();
                if (!$dividends) {
                    $dividends = $event->getConnectivity();
                } else {
                    $dividends->merge($event->getConnectivity());
                }
                $this->book->setConnection($dividends);

                break;

            case WalletInterestUpdated::class:
                /** @var WalletInterestUpdated $event */

                $this->book
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $dividends = $this->book->getInterest();
                if (!$dividends) {
                    $dividends = $event->getInterests();
                } else {
                    $dividends->merge($event->getInterests());
                }
                $this->book->setInterest($dividends);

                break;

            case WalletDividendsUpdated::class:
                /** @var WalletDividendsUpdated $event */

                $this->book
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $dividends = $this->book->getDividends();
                if (!$dividends) {
                    $dividends = $event->getDividends();
                } else {
                    $dividends->merge($event->getDividends());
                }
                $this->book->setDividends($dividends);

                break;

            case WalletCapitalUpdated::class:
                /** @var WalletCapitalUpdated $event */

                $this->book
                    ->setCapital($event->getCapital())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                break;
        }
    }
}
