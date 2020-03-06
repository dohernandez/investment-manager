<?php

namespace App\Infrastructure\Storage\ExchangeMoney;

use App\Application\ExchangeMoney\Repository\WalletRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\StockRepositoryInterface;
use App\Domain\ExchangeMoney\Wallet;

final class WalletRepository implements WalletRepositoryInterface
{
    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    public function __construct(ProjectionWalletRepositoryInterface $projectionWalletRepository)
    {
        $this->projectionWalletRepository = $projectionWalletRepository;
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        $projectionWallets = $this->projectionWalletRepository->findAll();

        if (empty($projectionWallets)){
            return [];
        }

        $wallets = [];

        foreach ($projectionWallets as $projectionWallet) {
            $wallets[] = new Wallet($projectionWallet->getCurrency());
        }

        return $wallets;
    }
}
