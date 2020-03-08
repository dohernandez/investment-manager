<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\UpdateDividendRetention;
use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Infrastructure\Exception\NotFoundException;

final class UpdateDividendRetentionHandler extends PositionDividendHandler
{
    /**
     * @var PositionRepositoryInterface
     */
    private $positionRepository;

    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    public function __construct(
        PositionRepositoryInterface $positionRepository,
        ProjectionPositionRepositoryInterface $projectionPositionRepository
    ) {
        $this->positionRepository = $positionRepository;
        $this->projectionPositionRepository = $projectionPositionRepository;
    }

    public function __invoke(UpdateDividendRetention $message)
    {
        $projectionPosition = $this->projectionPositionRepository->findByWalletStatus(
            $message->getId(),
            $message->getWalletId(),
            Position::STATUS_OPEN
        );

        if ($projectionPosition === null) {
            throw new NotFoundException(
                'Position not found', [
                'id'       => $message->getId(),
                'walletId' => $message->getWalletId(),
                'status'   => Position::STATUS_OPEN,
            ]
            );
        }

        $position = $this->positionRepository->find($projectionPosition->getId());

        $position->updateDividendRetention($message->getRetention());
        $this->positionRepository->save($position);

        return $this->createPositionDividend($position);
    }
}
