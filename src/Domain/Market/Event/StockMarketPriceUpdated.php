<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockMarketPrice;
use DateTime;

final class StockMarketPriceUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var StockMarketPrice
     */
    private $price;

    /**
     * @var DateTime
     */
    private $updatedAt;

    public function __construct(
        string $id,
        StockMarketPrice $price,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->price = $price;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPrice(): StockMarketPrice
    {
        return $this->price;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
