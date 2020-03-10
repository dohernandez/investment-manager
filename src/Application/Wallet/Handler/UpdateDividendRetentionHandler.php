<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\UpdateDividendRetention;
use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
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

    /**
     * @var ExchangeMoneyRepositoryInterface
     */
    private $exchangeMoneyRepository;

    public function __construct(
        PositionRepositoryInterface $positionRepository,
        ProjectionPositionRepositoryInterface $projectionPositionRepository,
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository
    ) {
        $this->positionRepository = $positionRepository;
        $this->projectionPositionRepository = $projectionPositionRepository;
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
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

        $exchangeMoneyRate = $this->exchangeMoneyRepository->findRate(
            $position->getStock()->getCurrency(),
            $position->getBook()->getCurrency()
        );

        $position->updateDividendRetention($message->getRetention(), $exchangeMoneyRate);
        $this->positionRepository->save($position);

        return $this->createPositionDividend($position);
    }
}
