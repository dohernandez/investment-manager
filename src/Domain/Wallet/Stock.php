<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;

final class Stock
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var Market
     */
    private $market;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Money|null
     */
    private $price;

    /**
     * @var Money|null
     */
    private $change;

    /**
     * @var Money|null
     */
    private $preClose;

    /**
     * @var Money|null
     */
    private $nextDividend;

    /**
     * @var DateTime|null
     */
    private $nextDividendExDate;

    /**
     * @var DateTime|null
     */
    private $prevDividendExDate;

    /**
     * @var Money|null
     */
    private $toPayDividend;

    /**
     * @var DateTime|null
     */
    private $toPayDividendDate;

    /**
     * @var string
     */
    private $notes;

    public function __construct(
        string $id,
        string $name,
        string $symbol,
        Market $market,
        ?Money $price = null,
        ?Money $change = null,
        ?Money $preClose = null,
        ?Money $nextDividend = null,
        ?DateTime $nextDividendExDate = null,
        ?DateTime $prevDividendExDate = null,
        ?Money $toPayDividend = null,
        ?DateTime $toPayDividendDate = null,
        string $notes = null
    ) {
        $this->id = $id;
        $this->symbol = $symbol;
        $this->market = $market;
        $this->name = $name;
        $this->price = $price;
        $this->change = $change;
        $this->preClose = $preClose;
        $this->nextDividend = $nextDividend;
        $this->nextDividendExDate = $nextDividendExDate;
        $this->prevDividendExDate = $prevDividendExDate;
        $this->toPayDividend = $toPayDividend;
        $this->toPayDividendDate = $toPayDividendDate;
        $this->notes = $notes;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getMarket(): Market
    {
        return $this->market;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function getChange(): ?Money
    {
        return $this->change;
    }

    public function getPreClose(): ?Money
    {
        return $this->preClose;
    }

    public function getTitle(): string
    {
        return sprintf(
            '%s (%s:%s)',
            $this->getName(),
            $this->getSymbol(),
            $this->getMarket()->getSymbol()
        );
    }

    public function getNextDividend(): ?Money
    {
        return $this->nextDividend;
    }

    public function getNextDividendExDate(): ?DateTime
    {
        return $this->nextDividendExDate;
    }

    public function getPrevDividendExDate(): ?DateTime
    {
        return $this->prevDividendExDate;
    }

    public function getToPayDividend(): ?Money
    {
        return $this->toPayDividend;
    }

    public function getToPayDividendDate(): ?DateTime
    {
        return $this->toPayDividendDate;
    }

    public function getCurrency(): Currency
    {
        return $this->price ? $this->price->getCurrency() : $this->market->getCurrency();
    }

    public function getNotes(): string
    {
        return $this->notes;
    }
}
