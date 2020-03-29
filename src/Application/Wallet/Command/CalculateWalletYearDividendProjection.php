<?php

namespace App\Application\Wallet\Command;

final class CalculateWalletYearDividendProjection
{
    /**
     * @var string
     */
    private $walletId;

    /**
     * @var int
     */
    private $year;

    public function __construct(string $walletId, int $year)
    {
        $this->walletId = $walletId;
        $this->year = $year;
    }

    public function getWalletId(): string
    {
        return $this->walletId;
    }

    public function getYear(): int
    {
        return $this->year;
    }
}
