<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Money;
use DateTime;

final class StockDelisted
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $delistedAt;

    public function __construct(string $id, DateTime $delistedAt)
    {
        $this->id = $id;
        $this->delistedAt = $delistedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDelistedAt(): DateTime
    {
        return $this->delistedAt;
    }
}
