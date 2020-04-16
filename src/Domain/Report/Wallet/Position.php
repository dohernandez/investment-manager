<?php

namespace App\Domain\Report\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class Position implements DataInterface
{
    use Data;

    /**
     * @var DateTime
     */
    private $dateAdded;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $invested;

    /**
     * @var Money|null
     */
    private $dividendCollected;

    /**
     * @var UpDown
     */
    private $benefits;

    /**
     * @var UpDown
     */
    private $changes;

    /**
     * @var Dividend|null
     */
    private $nextDividend;

    /**
     * @var Dividend|null
     */
    private $toPayDividend;

    public function __construct(
        DateTime $dateAdded,
        Stock $stock,
        int $amount,
        Money $capital,
        Money $invested,
        ?Money $dividendCollected,
        UpDown $benefits,
        UpDown $changes,
        ?Dividend $nextDividend,
        ?Dividend $toPayDividend
    ) {
        $this->dateAdded = $dateAdded;
        $this->stock = $stock;
        $this->amount = $amount;
        $this->capital = $capital;
        $this->invested = $invested;
        $this->dividendCollected = $dividendCollected;
        $this->benefits = $benefits;
        $this->changes = $changes;
        $this->nextDividend = $nextDividend;
        $this->toPayDividend = $toPayDividend;
    }

    public function getDateAdded(): DateTime
    {
        return $this->dateAdded;
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function getDividendCollected(): ?Money
    {
        return $this->dividendCollected;
    }

    public function getBenefits(): UpDown
    {
        return $this->benefits;
    }

    public function getChanges(): UpDown
    {
        return $this->changes;
    }

    public function getNextDividend(): ?Dividend
    {
        return $this->nextDividend;
    }

    public function getToPayDividend(): ?Dividend
    {
        return $this->toPayDividend;
    }
}
