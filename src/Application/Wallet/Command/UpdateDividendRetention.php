<?php

namespace App\Application\Wallet\Command;

use App\Infrastructure\Money\Money;

final class UpdateDividendRetention
{
    /**
     * @var string
     */
    private $walletId;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Money
     */
    private $retention;

    public function __construct(string $walletId, string $id, Money $retention)
    {
        $this->walletId = $walletId;
        $this->id = $id;
        $this->retention = $retention;
    }

    public function getWalletId(): string
    {
        return $this->walletId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRetention(): Money
    {
        return $this->retention;
    }
}
