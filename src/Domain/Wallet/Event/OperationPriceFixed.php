<?php

namespace App\Domain\Wallet\Event;

use App\Infrastructure\Money\Money;
use DateTime;

final class OperationPriceFixed
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Money
     */
    private $price;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    public function __construct(string $id, Money $price, ?DateTime $updatedAt = null)
    {
        $this->id = $id;
        $this->price = $price;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
