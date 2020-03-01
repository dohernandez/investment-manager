<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Event\WalletBuyOperationUpdated;
use App\Domain\Wallet\Event\WalletBuySellOperationUpdated;
use App\Domain\Wallet\Event\WalletCreated;
use App\Domain\Wallet\Event\WalletInvestmentIncreased;
use App\Domain\Wallet\Event\WalletSellOperationUpdated;
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
                $book->getCapital()->increase($invested),
                $book->getFunds()->increase($invested)
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

        $year = (string) Date::getYear($operation->getDateAt());
        $bookCommissionsYearEntry = BookEntry::createYearEntry($bookCommissions, $year);
        $bookCommissions->getEntries()->add($bookCommissionsYearEntry);

        $month = (string) Date::getMonth($operation->getDateAt());
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

        $year = (string) Date::getYear($operation->getDateAt());
        $bookCommissionsYearEntry = BookEntry::createYearEntry($bookCommissions, $year);
        $bookCommissions->getEntries()->add($bookCommissionsYearEntry);

        $month = (string) Date::getMonth($operation->getDateAt());
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

    protected function apply(Changed $changed)
    {
        $event = $changed->getPayload();

        switch ($changed->getEventName()) {
            case WalletCreated::class:
                /** @var WalletCreated $event */

                $this->name = $event->getName();
                $this->slug = $event->getSlug();

                $this->broker = $event->getBroker();
                $this->account = $event->getAccount();
                $this->book = $event->getBook();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                $this->accountId = $this->account->getId();

                break;

            case WalletInvestmentIncreased::class:
                /** @var WalletInvestmentIncreased $event */

                $this->book->setInvested($event->getInvested());
                $this->book->setCapital($event->getCapital());
                $this->book->setFunds($event->getFunds());

                $this->updatedAt = $changed->getCreatedAt();

                break;

            case WalletBuyOperationUpdated::class:
            case WalletSellOperationUpdated::class:
                /** @var WalletBuySellOperationUpdated $event */

                $this->book
                    ->setCapital($event->getCapital())
                    ->setFunds($event->getFunds())
                    ->setBenefits($event->getBenefits())
                    ->setPercentageBenefits($event->getPercentageBenefits());

                $commissions = $this->book->getCommissions();
                if (!$commissions) {
                     $commissions = $event->getCommissions();
                } else {
                    $commissions->merge($event->getCommissions());
                }
                $this->book->setCommissions($commissions);

                $this->updatedAt = $changed->getCreatedAt();

                break;
        }
    }
}
