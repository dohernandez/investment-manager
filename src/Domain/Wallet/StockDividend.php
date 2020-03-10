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

    public function __construct(DateTime $exDate)
    {
        $this->exDate = $exDate;
    }

    public function getExDate(): ?DateTime
    {
        return $this->exDate;
    }
}
