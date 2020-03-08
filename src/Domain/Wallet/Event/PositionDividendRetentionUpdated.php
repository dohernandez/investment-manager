<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Money\Money;
use DateTime;

final class PositionDividendRetentionUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Money|null
     */
    private $nextDividendAfterTaxes;

    /**
     * @var float|null
     */
    private $nextDividendYieldAfterTaxes;

    /**
     * @var BookEntry|null
     */
    private $dividendRetention;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    public function __construct(
        string $id,
        ?Money $nextDividendAfterTaxes = null,
        ?float $nextDividendYieldAfterTaxes = null,
        ?BookEntry $dividendRetention = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->nextDividendAfterTaxes = $nextDividendAfterTaxes;
        $this->nextDividendYieldAfterTaxes = $nextDividendYieldAfterTaxes;
        $this->dividendRetention = $dividendRetention;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNextDividendAfterTaxes(): ?Money
    {
        return $this->nextDividendAfterTaxes;
    }

    public function getNextDividendYieldAfterTaxes(): ?float
    {
        return $this->nextDividendYieldAfterTaxes;
    }

    public function getDividendRetention(): ?BookEntry
    {
        return $this->dividendRetention;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
