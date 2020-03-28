<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use DateTime;

final class WalletYearDividendProjectionCalculated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var BookEntry
     */
    private $yearDividendProjected;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    public function __construct(string $id, BookEntry $yearDividendProjected, ?DateTime $updatedAt = null)
    {
        $this->id = $id;
        $this->yearDividendProjected = $yearDividendProjected;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getYearDividendProjected(): BookEntry
    {
        return $this->yearDividendProjected;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
