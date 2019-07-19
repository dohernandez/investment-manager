<?php

namespace App\Entity;

use App\Repository\Criteria\PositionByCriteria;
use App\VO\Currency;
use App\VO\Money;
use App\VO\WalletDividendMonthMetadata;
use App\VO\WalletDividendYearMetadata;
use App\VO\WalletMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WalletRepository")
 */
class Wallet implements Entity
{
    const RATE_EXCHANGE_EUR_USD = 'EUR_USD';

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $name;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=false)
     */
    private $invested;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=false)
     */
    private $capital;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=false)
     */
    private $funds;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Broker", inversedBy="wallet")
     * @ORM\JoinColumn(nullable=false)
     */
    private $broker;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trade", mappedBy="wallet")
     */
    private $trades;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=false)
     */
    private $dividend;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=false)
     */
    private $commissions;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=false)
     */
    private $connection;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=false)
     */
    private $interest;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="wallet", cascade={"persist"})
     */
    private $operations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Position", mappedBy="wallet")
     */
    private $positions;

    /**
     * @var Currency
     *
     * @ORM\Column(type="currency", nullable=false)
     */
    private $currency;

    /**
     * @var WalletMetadata
     *
     * @ORM\Column(type="wallet_metadata", nullable=true)
     */
    private $metadata;

    public function __construct()
    {
        $this->trades = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->positions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function setInvested(?Money $invested): self
    {
        $this->invested = $invested;

        return $this;
    }

    public function increaseInvested(?Money $invested): self
    {
        if (!$invested) {
            return $this;
        }

        $this->setInvested($this->getInvested()->increase($invested));

        return $this;
    }

    public function decreaseInvested(?Money $invested): self
    {
        if (!$invested) {
            return $this;
        }

        $this->setInvested($this->getInvested()->decrease($invested));

        return $this;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function setCapital(?Money $capital): self
    {
        if ($capital === null) {
            $capital = Money::fromCurrency($this->getCurrency());
        }

        $this->capital = $capital;

        return $this;
    }

    public function getNetCapital(): Money
    {
        return $this->getCapital()->increase($this->getFunds());
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function setFunds(?Money $funds): self
    {
        if ($funds === null) {
            $funds = Money::fromCurrency($this->getCurrency());
        }

        $this->funds = $funds;

        return $this;
    }

    public function getBroker(): ?Broker
    {
        return $this->broker;
    }

    public function setBroker(?Broker $broker): self
    {
        $this->broker = $broker;
        $this->currency = $broker->getCurrency();

        $this->setInvested(null);
        $this->setCapital(null);
        $this->setFunds(null);
        $this->setDividend(null);
        $this->setCommissions(null);
        $this->setConnection(null);
        $this->setInterest(null);
        $this->setMetadata(null);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    public function addFunds(Money $funds): self
    {
        $this->increaseInvested($funds)
            ->increaseFunds($funds)
        ;

        return $this;
    }

    public function subtractFunds(Money $funds): self
    {
        $this->decreaseInvested($funds)
            ->decreaseFunds($funds);

        return $this;
    }

    public function increaseFunds(?Money $funds): self
    {
        if (!$funds) {
            return $this;
        }

        $this->setFunds($this->getFunds()->increase($funds));

        return $this;
    }

    public function decreaseFunds(?Money $funds): self
    {
        if (!$funds) {
            return $this;
        }

        $this->setFunds($this->getFunds()->decrease($funds));

        return $this;
    }

    public function getBenefits(): Money
    {
        return $this->getCapital()->increase($this->getFunds())->decrease($this->getInvested());
    }

    /**
     * @return Collection|Trade[]
     */
    public function getTrades(): Collection
    {
        return $this->trades;
    }

    public function addTrade(Trade $trade): self
    {
        if (!$this->trades->contains($trade)) {
            $this->trades[] = $trade;
            $trade->setWallet($this);
        }

        return $this;
    }

    public function removeTrade(Trade $trade): self
    {
        if ($this->trades->contains($trade)) {
            $this->trades->removeElement($trade);
            // set the owning side to null (unless already changed)
            if ($trade->getWallet() === $this) {
                $trade->setWallet(null);
            }
        }

        return $this;
    }

    public function getDividend(): Money
    {
        return $this->dividend;
    }

    public function setDividend(?Money $dividend): self
    {
        if ($dividend === null) {
            $dividend = Money::fromCurrency($this->getCurrency());
        }

        $this->dividend = $dividend;

        return $this;
    }

    public function increaseDividend(Money $dividend): self
    {
        $this->setDividend($this->getDividend()->increase($dividend));

        return $this;
    }

    /**
     * Alias of increaseDividend
     *
     * @param Money $dividend
     *
     * @return Wallet
     */
    public function addDividend(Money $dividend): self
    {
        $this->increaseDividend($dividend);

        return $this;
    }

    public function getCommissions(): Money
    {
        return $this->commissions;
    }

    public function setCommissions(?Money $commissions): self
    {
        if ($commissions === null) {
            $commissions = Money::fromCurrency($this->getCurrency());
        }

        $this->commissions = $commissions;

        return $this;
    }

    public function increaseCommissions(Money $commissions): self
    {
        $this->setCommissions($this->getCommissions()->increase($commissions));

        return $this;
    }

    public function getConnection(): Money
    {
        return $this->connection;
    }

    public function setConnection(?Money $connection): self
    {
        if ($connection === null) {
            $connection = Money::fromCurrency($this->getCurrency());
        }

        $this->connection = $connection;

        return $this;
    }

    public function increaseConnection(Money $connection): self
    {
        $this->setConnection($this->getConnection()->increase($connection));

        return $this;
    }

    public function getInterest(): Money
    {
        return $this->interest;
    }

    public function setInterest(?Money $interest): self
    {
        if ($interest === null) {
            $interest = Money::fromCurrency($this->getCurrency());
        }

        $this->interest = $interest;

        return $this;
    }

    public function increaseInterest(Money $interest): self
    {
        $this->setInterest($this->getInterest()->increase($interest));

        return $this;
    }

    public function addExpenses(Money $expenses, string $type): self
    {
        switch ($type){
            case Operation::TYPE_CONNECTIVITY:
                $this->increaseConnection($expenses);
                break;
            case Operation::TYPE_INTEREST:
                $this->increaseInterest($expenses);
                break;
            default:
                throw new \LogicException(sprintf('expenses "%s" not supported.', $type));
        }

        return $this;
    }

    /**
     * @return Collection|Operation[]
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    /**
     * Add an operation to the collection and update the wallet based on the operation type.
     * @param Operation $operation
     *
     * @return Wallet
     */
    public function addOperation(Operation $operation): self
    {
        if (!$this->operations->contains($operation)) {

            $netValue = $operation->getNetValue();

            switch ($operation->getType()) {
                case Operation::TYPE_BUY:
                        $this->decreaseFunds($netValue);
                        $this->increaseCommissions($operation->getFinalCommissionPaid());
                    break;
                case Operation::TYPE_SELL:
                        $this->increaseFunds($netValue);
                        $this->increaseCommissions($operation->getFinalCommissionPaid());
                    break;
                case Operation::TYPE_DIVIDEND:
                    $this->increaseFunds($netValue);
                    $this->increaseDividend($netValue);

                    // update metadata dividend paid
                    $lastPaidDividend = $operation->getStock()->lastPaidDividendAtDate($operation->getDateAt());
                    if ($lastPaidDividend) {
                        $year = $lastPaidDividend->getExDate()->format('Y');
                        $month = $lastPaidDividend->getExDate()->format('m');
                    } else {
                        $year = $operation->getDateAt()->format('Y');
                        $month = $operation->getDateAt()->format('m');
                    }

                    $metadata = $this->getMetadata();
                    if ($metadata === null) {
                        $metadata = new WalletMetadata();
                    }

                    // Setting dividend year metadata
                    $dividendYearMetadata = $metadata->getDividendYear($year);
                    if ($dividendYearMetadata === null) {
                        $dividendYearMetadata = WalletDividendYearMetadata::fromYear($year);
                    }
                    $dividendYearMetadata = $dividendYearMetadata->increasePaid($netValue);

                    // Setting dividend month metadata
                    $dividendMonthMetadata = $dividendYearMetadata->getDividendMonth($month);
                    if ($dividendMonthMetadata === null) {
                        $dividendMonthMetadata = WalletDividendMonthMetadata::fromMonth($month);
                    }
                    $dividendMonthMetadata = $dividendMonthMetadata->increasePaid($netValue);

                    $dividendYearMetadata = $dividendYearMetadata->setDividendMonth($month, $dividendMonthMetadata);

                    $this->setMetadata($metadata->setDividendYear($year, $dividendYearMetadata));
                    break;
                case Operation::TYPE_INTEREST:
                        $this->decreaseFunds($netValue);
                        $this->increaseInterest($netValue);
                    break;
                case Operation::TYPE_CONNECTIVITY:
                        $this->decreaseFunds($netValue);
                        $this->increaseConnection($netValue);
                    break;
                default:
                    throw new \LogicException(sprintf('operation "%s" not supported.', $operation->getType()));
            }

            $this->operations[] = $operation;
            $operation->setWallet($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getWallet() === $this) {
                $operation->setWallet(null);
            }
        }

        return $this;
    }

    public function getPositions(string $status = null): Collection
    {
        if ($status !== null && $status !== '') {
            return $this->positions->matching(PositionByCriteria::byStatus($status));
        }

        return $this->positions;
    }

    public function findPositionByStockAndOpen(Stock $stock): ?Position
    {
        $matching = $this->positions->matching(PositionByCriteria::ByStockAndOpen($stock));
        if ($matching->isEmpty()) {
            return null;
        }

        return $matching->first();
    }

    public function findPositionByStockOpenDateAt(Stock $stock, \DateTimeInterface $datedAt): ?Position
    {
        $matching = $this->positions->matching(PositionByCriteria::ByStockOpenDateAt($stock, $datedAt));
        if ($matching->isEmpty()) {
            return null;
        }

        return $matching->first();
    }

    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions[] = $position;
            $position->setWallet($this);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->contains($position)) {
            $this->positions->removeElement($position);
            // set the owning side to null (unless already changed)
            if ($position->getWallet() === $this) {
                $position->setWallet(null);
            }
        }

        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getMetadata(): ?WalletMetadata
    {
        return $this->metadata;
    }

    public function setMetadata(?WalletMetadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
