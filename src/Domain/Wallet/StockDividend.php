<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class StockDividend implements DataInterface
{
    use Data;

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
