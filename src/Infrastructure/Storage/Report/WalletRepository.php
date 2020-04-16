<?php

namespace App\Infrastructure\Storage\Report;

use App\Application\Report\Repository\WalletRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Domain\Report\Wallet\Wallet;
use App\Infrastructure\Storage\Report\Hydrate\HydrateInterface;

final class WalletRepository implements WalletRepositoryInterface
{
    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    /**
     * @var HydrateInterface
     */
    private $walletHydrate;

    public function __construct(
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        HydrateInterface $walletHydrate
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->walletHydrate = $walletHydrate;
    }

    public function find(string $id): Wallet
    {
        return $this->walletHydrate->hydrate(
            $this->projectionWalletRepository->find($id)
        );
    }
}
