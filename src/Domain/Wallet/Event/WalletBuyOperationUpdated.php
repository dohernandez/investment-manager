<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Money\Money;
use DateTime;

final class WalletBuyOperationUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $dateAt;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $funds;

    /**
     * @var BookEntry
     */
    private $commissions;

    public function __construct(
        string $id,
        DateTime $dateAt,
        Money $capital,
        Money $funds,
        BookEntry $commissions
    ) {
        $this->id = $id;
        $this->dateAt = $dateAt;
        $this->capital = $capital;
        $this->funds = $funds;
        $this->commissions = $commissions;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function getCommissions(): BookEntry
    {
        return $this->commissions;
    }
}
