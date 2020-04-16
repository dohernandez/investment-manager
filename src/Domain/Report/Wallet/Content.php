<?php

namespace App\Domain\Report\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class Content implements DataInterface
{
    use Data;

    /**
     * @var Wallet
     */
    private $wallet;

    /**
     * @var Position[]
     */
    private $positions;

    public function __construct(Wallet $wallet, array $positions)
    {
        $this->wallet = $wallet;
        $this->positions = $positions;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getPositions(): array
    {
        return $this->positions;
    }
}
