<?php

namespace App\Application\Account\Command;

use App\Infrastructure\Money\Money;

final class WithdrawMoneyCommand
{
    public function __construct(string $id, Money $money)
    {
        $this->id = $id;
        $this->money = $money;
    }

    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @var Money
     */
    private $money;

    /**
     * @return Money
     */
    public function getMoney(): Money
    {
        return $this->money;
    }
}
