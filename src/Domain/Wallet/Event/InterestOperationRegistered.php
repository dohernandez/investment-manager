<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Stock;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Money\Money;
use DateTime;

final class InterestOperationRegistered
{
    /**
     * @var Wallet
     */
    private $wallet;

    /**
     * @var DateTime
     */
    private $dateAt;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Money
     */
    private $value;

    /**
     * @var string
     */
    private $id;

    public function __construct(
        string $id,
        Wallet $wallet,
        DateTime $dateAt,
        string $type,
        Money $value
    )
    {
        $this->wallet = $wallet;
        $this->dateAt = $dateAt;
        $this->type = $type;
        $this->value = $value;
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): Money
    {
        return $this->value;
    }
}
