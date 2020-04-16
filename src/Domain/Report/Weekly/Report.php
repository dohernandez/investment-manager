<?php

namespace App\Domain\Report\Weekly;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;

use function array_map;

final class Report implements DataInterface
{
    use Data;

    /**
     * @var Wallet
     */
    private $wallet;

    /**
     * @var Stock[]
     */
    private $walletStocks;

    /**
     * @var Stock[]
     */
    private $moverStocks;

    /**
     * @var Stock[]
     */
    private $shakerStocks;

    private function __construct(Wallet $wallet, array $walletStocks, array $moverStocks, array $shakerStocks)
    {
        $this->wallet = $wallet;
        $this->walletStocks = $walletStocks;
        $this->moverStocks = $moverStocks;
        $this->shakerStocks = $shakerStocks;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getWalletStocks(): array
    {
        return $this->walletStocks;
    }

    public function getMoverStocks(): array
    {
        return $this->moverStocks;
    }

    public function getShakerStocks(): array
    {
        return $this->shakerStocks;
    }

    public static function createReport(Wallet $wallet, array $walletStocks, array $moverStocks, array $shakerStocks)
    {
        return new static($wallet, $walletStocks, $moverStocks, $shakerStocks);
    }
}
