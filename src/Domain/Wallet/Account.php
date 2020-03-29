<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Money;

final class Account
{
    public function __construct(string $id, string $name, string $accountNo, ?Money $balance = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->accountNo = $accountNo;
        $this->balance = $balance;
    }

    /** @var string */
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
    private $accountNo;

    public function getAccountNo(): string
    {
        return $this->accountNo;
    }

    public function getTitle()
    {
        return sprintf('%s - %s', $this->getName(), $this->getAccountNo());
    }

    /**
     * @var Money|null
     */
    private $balance;

    public function getBalance(): ?Money
    {
        return $this->balance;
    }
}
