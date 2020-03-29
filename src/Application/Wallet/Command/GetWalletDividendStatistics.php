<?php

namespace App\Application\Wallet\Command;

use DateTime;

final class GetWalletDividendStatistics
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var DateTime|null
     */
    private $date;

    public function __construct(string $id, ?DateTime $date = null, int $limit = 3)
    {
        $this->id = $id;
        $this->limit = $limit;
        $this->date = $date;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
