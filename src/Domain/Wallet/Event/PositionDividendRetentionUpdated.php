<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class PositionDividendRetentionUpdated implements DataInterface
{
    use Data;

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

    /**
     * @var Money|null
     */
    private $toPayDividendAfterTaxes;

    /**
     * @var float|null
     */
    private $toPayDividendYieldAfterTaxes;

    public function __construct(
        string $id,
        ?Money $nextDividendAfterTaxes = null,
        ?float $nextDividendYieldAfterTaxes = null,
        ?Money $toPayDividendAfterTaxes = null,
        ?float $toPayDividendYieldAfterTaxes = null,
        ?BookEntry $dividendRetention = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->nextDividendAfterTaxes = $nextDividendAfterTaxes;
        $this->nextDividendYieldAfterTaxes = $nextDividendYieldAfterTaxes;
        $this->dividendRetention = $dividendRetention;
        $this->updatedAt = $updatedAt;
        $this->toPayDividendAfterTaxes = $toPayDividendAfterTaxes;
        $this->toPayDividendYieldAfterTaxes = $toPayDividendYieldAfterTaxes;
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

    public function getToPayDividendAfterTaxes(): ?Money
    {
        return $this->toPayDividendAfterTaxes;
    }

    public function getToPayDividendYieldAfterTaxes(): ?float
    {
        return $this->toPayDividendYieldAfterTaxes;
    }
}
