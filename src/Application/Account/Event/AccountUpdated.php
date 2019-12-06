<?php

namespace App\Application\Account\Event;

use App\Infrastructure\Money\Money;
use DateTimeImmutable;

final class AccountUpdated
{
    public function __construct(string $id, Money $balance, DateTimeImmutable $updatedAt)
    {
        $this->id = $id;
        $this->balance = $balance;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @var string
     */
    private $id;

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @var Money
     */
    private $balance;

    public function getBalance(): Money
    {
        return $this->balance;
    }

    /**
     * @var DateTimeImmutable
     */
    private $updatedAt;

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
