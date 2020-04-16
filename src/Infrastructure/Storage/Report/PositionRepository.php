<?php

namespace App\Infrastructure\Storage\Report;

use App\Application\Report\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Domain\Wallet\Position as ProjectionPosition;
use App\Infrastructure\Storage\Report\Hydrate\Wallet\PositionHydrate;

final class PositionRepository implements PositionRepositoryInterface
{
    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    /**
     * @var PositionHydrate
     */
    private $positionHydrate;

    public function __construct(
        ProjectionPositionRepositoryInterface $projectionPositionRepository,
        PositionHydrate $positionHydrate
    ) {
        $this->projectionPositionRepository = $projectionPositionRepository;
        $this->positionHydrate = $positionHydrate;
    }

    /**
     * @inheritDoc
     */
    public function findAllOpenByWallet(string $walletId): array
    {
        $positions = [];
        $result = $this->projectionPositionRepository
            ->findAllByWalletStatus($walletId, ProjectionPosition::STATUS_OPEN);

        foreach ($result as $position) {
            $positions[] = $this->positionHydrate->hydrate($position);
        }

        return $positions;
    }
}
