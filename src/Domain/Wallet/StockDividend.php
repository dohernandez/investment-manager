<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;

final class StockDividend
{
    /**
     * @var DateTime|null
     */
    private $exDate;

    /**
     * @var Money|null
     */
    private $value;

    public function __construct(DateTime $exDate, Money $value)
    {
        $this->exDate = $exDate;
        $this->value = $value;
    }

    public function getExDate(): ?DateTime
    {
        return $this->exDate;
    }

    public function getValue(): ?Money
    {
        return $this->value;
    }
}
