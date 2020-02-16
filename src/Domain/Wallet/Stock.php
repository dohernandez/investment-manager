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
    private $nextDividend;

    /**
     * @var DateTime|null
     */
    private $nextDividendExDate;

    /**
     * @var Money|null
     */
    private $toPayDividend;

    /**
     * @var DateTime|null
     */
    private $toPayDividendDate;

    public function __construct(
        string $id,
        string $name,
        string $symbol,
        Market $market,
        ?Money $price = null,
        ?Money $nextDividend = null,
        ?DateTime $nextDividendExDate = null,
        ?Money $toPayDividend = null,
        ?DateTime $toPayDividendDate = null
    ) {
        $this->id = $id;
        $this->symbol = $symbol;
        $this->market = $market;
        $this->name = $name;
        $this->price = $price;
        $this->nextDividend = $nextDividend;
        $this->nextDividendExDate = $nextDividendExDate;
        $this->toPayDividend = $toPayDividend;
        $this->toPayDividendDate = $toPayDividendDate;
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
        return $this->price->getCurrency();
    }
}
