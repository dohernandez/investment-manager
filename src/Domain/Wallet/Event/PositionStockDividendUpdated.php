<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Stock;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class PositionStockDividendUpdated implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Stock|null
     */
    private $stock;

    /**
     * @var Money|null
     */
    private $nextDividend;

    /**
     * @var float|null
     */
    private $nextDividendYield;

    /**
     * @var Money|null
     */
    private $nextDividendAfterTaxes;

    /**
     * @var float|null
     */
    private $nextDividendYieldAfterTaxes;

    /**
     * @var Money|null
     */
    private $toPayDividend;

    /**
     * @var float|null
     */
    private $toPayDividendYield;

    /**
     * @var Money|null
     */
    private $toPayDividendAfterTaxes;

    /**
     * @var float|null
     */
    private $toPayDividendYieldAfterTaxes;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    public function __construct(
        string $id,
        Stock $stock,
        ?Money $nextDividend = null,
        ?float $nextDividendYield = null,
        ?Money $nextDividendAfterTaxes = null,
        ?float $nextDividendYieldAfterTaxes = null,
        ?Money $toPayDividend = null,
        ?float $toPayDividendYield = null,
        ?Money $toPayDividendAfterTaxes = null,
        ?float $toPayDividendYieldAfterTaxes = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->stock = $stock;
        $this->nextDividend = $nextDividend;
        $this->nextDividendYield = $nextDividendYield;
        $this->nextDividendAfterTaxes = $nextDividendAfterTaxes;
        $this->nextDividendYieldAfterTaxes = $nextDividendYieldAfterTaxes;
        $this->toPayDividend = $toPayDividend;
        $this->toPayDividendYield = $toPayDividendYield;
        $this->toPayDividendAfterTaxes = $toPayDividendAfterTaxes;
        $this->toPayDividendYieldAfterTaxes = $toPayDividendYieldAfterTaxes;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function getNextDividend(): ?Money
    {
        return $this->nextDividend;
    }

    public function getNextDividendYield(): ?float
    {
        return $this->nextDividendYield;
    }

    public function getNextDividendAfterTaxes(): ?Money
    {
        return $this->nextDividendAfterTaxes;
    }

    public function getNextDividendYieldAfterTaxes(): ?float
    {
        return $this->nextDividendYieldAfterTaxes;
    }

    public function getToPayDividend(): ?Money
    {
        return $this->toPayDividend;
    }

    public function getToPayDividendYield(): ?float
    {
        return $this->toPayDividendYield;
    }

    public function getToPayDividendAfterTaxes(): ?Money
    {
        return $this->toPayDividendAfterTaxes;
    }

    public function getToPayDividendYieldAfterTaxes(): ?float
    {
        return $this->toPayDividendYieldAfterTaxes;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
