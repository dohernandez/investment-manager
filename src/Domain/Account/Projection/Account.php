<?php

namespace App\Domain\Account\Projection;

use App\Infrastructure\Money\Money;
use DateTimeImmutable;

class Account
{
    public function __construct(string $id)
    {
        $this->id = $id;
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @var string
     */
    private $type;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @var string
     */
    private $accountNo;

    public function getAccountNo(): string
    {
        return $this->accountNo;
    }

    public function setAccountNo(string $accountNo): self
    {
        $this->accountNo = $accountNo;

        return $this;
    }

    /**
     * @var Money
     */
    private $balance;

    /**
     * @return Money
     */
    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function setBalance(Money $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @var DateTimeImmutable
     */
    private $createdAt;

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @var DateTimeImmutable
     */
    private $updatedAt;

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
