<?php

namespace App\Application\Wallet\Command;

final class GetPositionDividends
{
    /**
     * @var string
     */
    private $walletId;

    /**
     * @var string
     */
    private $positionStatus;

    public function __construct(string $walletId, string $positionStatus)
    {
        $this->walletId = $walletId;
        $this->positionStatus = $positionStatus;
    }

    public function getWalletId(): string
    {
        return $this->walletId;
    }

    public function getPositionStatus(): string
    {
        return $this->positionStatus;
    }
}
