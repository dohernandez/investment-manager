<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Money;

final class BookEntryMetadata
{
    /**
     * @var Money
     */
    private $moneyOriginalCurrency;

    /**
     * @var Rate|null
     */
    private $rate;

    public function __construct(Rate $rate, ?Money $moneyOriginalCurrency = null)
    {
        $this->moneyOriginalCurrency = $moneyOriginalCurrency;
        $this->rate = $rate;
    }

    public function getMoneyOriginalCurrency(): Money
    {
        return $this->moneyOriginalCurrency;
    }

    public function getRate(): ?Rate
    {
        return $this->rate;
    }

    public function updateRate(?Rate $rate): self
    {
        return new static($rate, $this->moneyOriginalCurrency);
    }
}
