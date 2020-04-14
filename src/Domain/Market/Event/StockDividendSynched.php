<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockDividend;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use DateTime;

final class StockDividendSynched implements DataInterface
{
    use Data;

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
        ?DateTime $synchedAt,
        ?float $dividendYield = null
    ) {
        $this->id = $id;
        $this->synchedAt = $synchedAt;
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
