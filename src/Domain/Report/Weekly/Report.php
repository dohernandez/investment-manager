<?php

namespace App\Domain\Report\Weekly;

use App\Infrastructure\Doctrine\DBAL\DomainInterface;

use function array_map;

final class Report implements DomainInterface
{
    private const DBAL_KEY_WALLET = 'wallet';
    private const DBAL_KEY_WALLET_STOCKS = 'wallet_stocks';
    private const DBAL_KEY_MOVERS_STOCKS = 'movers_stocks';
    private const DBAL_KEY_SHAKER_STOCKS = 'shaker_stocks';

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

    public function marshalDBAL(): array
    {
        return [
            self::DBAL_KEY_WALLET => $this->wallet->marshalDBAL(),

            self::DBAL_KEY_WALLET_STOCKS => array_map(
                function (Stock $stock) {
                    return $stock->marshalDBAL();
                },
                $this->getWalletStocks()
            ),

            self::DBAL_KEY_MOVERS_STOCKS => array_map(
                function (Stock $stock) {
                    return $stock->marshalDBAL();
                },
                $this->getMoverStocks()
            ),

            self::DBAL_KEY_SHAKER_STOCKS => array_map(
                function (Stock $stock) {
                    return $stock->marshalDBAL();
                },
                $this->getShakerStocks()
            ),
        ];
    }

    public static function unMarshalDBAL(array $value)
    {
        return new static(
            Wallet::unMarshalDBAL($value[self::DBAL_KEY_WALLET]),
            array_map(
                function (Stock $stock) {
                    return $stock->marshalDBAL();
                },
                $value[self::DBAL_KEY_WALLET_STOCKS]
            ),
            array_map(
                function (Stock $stock) {
                    return $stock->marshalDBAL();
                },
                $value[self::DBAL_KEY_MOVERS_STOCKS]
            ),
            array_map(
                function (Stock $stock) {
                    return $stock->marshalDBAL();
                },
                $value[self::DBAL_KEY_SHAKER_STOCKS]
            )
        );
    }
}
