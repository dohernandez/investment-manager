<?php

namespace App\Application\Wallet\Decorator;

use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Domain\Wallet\Wallet;

final class WalletOpenPositionDecorate implements WalletPositionDecorateInterface
{
    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    public function __construct(ProjectionPositionRepositoryInterface $projectionPositionRepository)
    {
        $this->projectionPositionRepository = $projectionPositionRepository;
    }

    public function decorate(Wallet $wallet)
    {
        $positions = $this->projectionPositionRepository->findAllByWalletStatus(
            $wallet->getId(),
            Position::STATUS_OPEN
        );

        $wallet->setPositions($positions);
    }
}
