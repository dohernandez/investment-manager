<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockDividend;
use DateTime;

final class StockDividendSynched
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var StockDividend|null
     */
    private $nextDividend;

    /**
     * @var StockDividend|null
     */
    private $toPayDividend;

    /**
     * @var float|null
     */
    private $dividendYield;

    /**
     * @var DateTime
     */
    private $synchedAt;

    public function __construct(
        string $id,
        ?StockDividend $nextDividend,
        ?StockDividend $toPayDividend,
        DateTime $updatedAt,
        ?float $dividendYield = null
    ) {
        $this->id = $id;
        $this->synchedAt = $updatedAt;
        $this->nextDividend = $nextDividend;
        $this->toPayDividend = $toPayDividend;
        $this->dividendYield = $dividendYield;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNextDividend(): ?StockDividend
    {
        return $this->nextDividend;
    }

    public function getToPayDividend(): ?StockDividend
    {
        return $this->toPayDividend;
    }

    public function getDividendYield(): ?float
    {
        return $this->dividendYield;
    }

    public function getSynchedAt(): DateTime
    {
        return $this->synchedAt;
    }
}
