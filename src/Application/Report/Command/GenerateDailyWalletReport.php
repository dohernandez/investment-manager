<?php

namespace App\Application\Report\Command;

use DateTime;

final class GenerateDailyWalletReport
{
    /**
     * @var string
     */
    private $walletId;

    /**
     * @var DateTime
     */
    private $dateAt;

    public function __construct(string $walletId, DateTime $dateAt)
    {
        $this->walletId = $walletId;
        $this->dateAt = $dateAt;
    }

    public function getWalletId(): string
    {
        return $this->walletId;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
    }
}
