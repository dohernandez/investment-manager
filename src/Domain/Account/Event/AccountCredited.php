<?php

namespace App\Domain\Account\Event;

use App\Infrastructure\Aggregator\Event;
use App\Infrastructure\Money\Money;

final class AccountCredited
{
    public function __construct(Money $money)
    {
        $this->money = $money;
    }

    /**
     * @var Money
     */
    private $money;

    public function getMoney(): Money
    {
        return $this->money;
    }
}
