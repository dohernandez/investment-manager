<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\MarketPrice;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use DateTime;

final class StockPriceUpdated implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var MarketPrice
     */
    private $price;

    /**
     * @var float|null
     */
    private $dividendYield;

    /**
     * @var DateTime
     */
    private $updatedAt;

    public function __construct(
        string $id,
        MarketPrice $price,
        DateTime $updatedAt,
        ?float $dividendYield = null
    ) {
        $this->id = $id;
        $this->price = $price;
        $this->updatedAt = $updatedAt;
        $this->dividendYield = $dividendYield;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPrice(): ?MarketPrice
    {
        return $this->price;
    }

    public function getDividendYield(): ?float
    {
        return $this->dividendYield;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
