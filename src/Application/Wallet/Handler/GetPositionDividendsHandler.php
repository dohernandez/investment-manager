<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\GetPositionDividends;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;

class GetPositionDividendsHandler extends PositionDividendHandler
{
    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    public function __construct(ProjectionPositionRepositoryInterface $projectionPositionRepository)
    {
        $this->projectionPositionRepository = $projectionPositionRepository;
    }

    public function __invoke(GetPositionDividends $message)
    {
        if ($message->getPositionStatus()) {
            $positions = $this->projectionPositionRepository->findAllByWalletStatus(
                $message->getWalletId(),
                $message->getPositionStatus()
            );
        } else {
            $positions = $this->projectionPositionRepository->findAllByWallet($message->getWalletId());
        }

        $positionDividends = [];
        foreach ($positions as $position) {
            $positionDividends[] = $this->createPositionDividend($position);
        }

        return $positionDividends;
    }
}
