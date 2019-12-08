<?php

namespace App\Application\Account\Event;

use App\Infrastructure\Money\Money;
use DateTime;

final class AccountCreated
{
    public function __construct(
        string $id,
        string $name,
        string $type,
        string $accountNo,
        Money $balance,
        DateTime $createdAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->accountNo = $accountNo;
        $this->createdAt = $createdAt;
        $this->balance = $balance;
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
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $type;

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @var string
     */
    private $accountNo;

    public function getAccountNo(): string
    {
        return $this->accountNo;
    }

    /**
     * @var DateTime
     */
    private $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @var Money
     */
    private $balance;

    public function getBalance(): Money
    {
        return $this->balance;
    }
}
