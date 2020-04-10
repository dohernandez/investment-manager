<?php

namespace App\Application\Wallet\Decorator;

use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\StockDividendRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Reflection\PropertySetter;
use Doctrine\Common\Collections\ArrayCollection;

final class WalletOpenPositionDecorate implements WalletPositionDecorateInterface
{
    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    /**
     * @var StockDividendRepositoryInterface
     */
    private $stockDividendRepository;

    public function __construct(
        ProjectionPositionRepositoryInterface $projectionPositionRepository,
        StockDividendRepositoryInterface $stockDividendRepository
    ) {
        $this->projectionPositionRepository = $projectionPositionRepository;
        $this->stockDividendRepository = $stockDividendRepository;
    }

    public function decorate(Wallet $wallet)
    {
        $positions = $this->projectionPositionRepository->findAllByWalletStatus(
            $wallet->getId(),
            Position::STATUS_OPEN
        );

        foreach ($positions as $position) {
            $stock = $position->getStock();
            $stock = $stock->appendStockDividends(
                new ArrayCollection(
                    $this->stockDividendRepository->findAllExDateTimeWindow(
                        $stock->getId(),
                        Date::getDateTimeBeginYear(),
                        $position->getClosedAt()
                    )
                )
            );

            PropertySetter::setValueProperty($position, 'stock', $stock);
        }

        $wallet->setPositions($positions);
    }
}
