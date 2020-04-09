<?php

namespace App\Application\Wallet\Decorator;

use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\StockDividendRepositoryInterface;
use App\Application\Wallet\Repository\StockRepositoryInterface;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Reflection\PropertySetter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

final class WalletOpenOrOpenedPositionYearWithDividendsDecorate implements
    WalletOpenOrOpenedPositionYearWithDividendsDecorateInterface
{
    /**
     * @var int
     */
    private $year;

    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    /**
     * @var StockRepositoryInterface
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
        $year = $this->year ? $this->year : Date::getYear(new DateTime());
        $positions = $this->projectionPositionRepository->findAllOpenOrHasOpenedByWalletInYear(
            $wallet->getId(),
            $year
        );

        foreach ($positions as $position) {
            $stock = $position->getStock();
            $stock = $stock->appendStockDividends(
                new ArrayCollection(
                    $this->stockDividendRepository->findAllExDateTimeWindow(
                        $stock->getId(),
                        Date::getDateTimeBeginYear($year),
                        $position->getClosedAt()
                    )
                )
            );

            PropertySetter::setValueProperty($position, 'stock', $stock);
        }

        $wallet->setPositions($positions);
    }

    public function setYear(int $year)
    {
        $this->year = $year;
    }
}
