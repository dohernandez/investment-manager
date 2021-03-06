<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\StockRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Market\Event\StockPriceUpdated;
use App\Domain\Wallet\Position;
use App\Infrastructure\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StockPriceUpdatedSubscriber implements EventSubscriberInterface
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
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var ExchangeMoneyRepositoryInterface
     */
    private $exchangeMoneyRepository;

    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    public function __construct(
        PositionRepositoryInterface $positionRepository,
        ProjectionPositionRepositoryInterface $projectionPositionRepository,
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        WalletRepositoryInterface $walletRepository,
        StockRepositoryInterface $stockRepository,
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository
    ) {
        $this->positionRepository = $positionRepository;
        $this->projectionPositionRepository = $projectionPositionRepository;
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->walletRepository = $walletRepository;
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            StockPriceUpdated::class => ['onStockPriceUpdated', 100],
        ];
    }

    public function onStockPriceUpdated(StockPriceUpdated $event)
    {
        $wallets = $this->projectionWalletRepository->findAllByStockInOpenPosition($event->getId());

        foreach ($wallets as $wallet) {
            $wallet = $this->walletRepository->find($wallet->getId());
            if ($wallet === null) {
                throw new NotFoundException(
                    'Wallet not found',
                    [
                        'id' => $wallet->getId()
                    ]
                );
            }

            $projectionPosition = $this->projectionPositionRepository->findByWalletStock(
                $wallet->getId(),
                $event->getId(),
                Position::STATUS_OPEN
            );

            if (!$projectionPosition) {
                continue;
            }

            $position = $this->positionRepository->find($projectionPosition->getId());
            if (!$position) {
                throw new NotFoundException(
                    'Position not found',
                    [
                        'id'       => $projectionPosition->getId(),
                        'walletId' => $wallet->getId(),
                        'stockId'  => $event->getId(),
                        'status'   => Position::STATUS_OPEN,
                    ]
                );
            }

            $stock = $this->stockRepository->find($event->getId());
            if (!$stock) {
                throw new NotFoundException(
                    'Stock not found',
                    [
                        'id'       => $event->getId(),
                    ]
                );
            }

            $exchangeMoneyRate = $this->exchangeMoneyRepository->findRate(
                $stock->getCurrency(),
                $wallet->getCurrency()
            );

            $oldPositionCapital = $position->getCapital();

            $position->updateStockPrice($stock, $exchangeMoneyRate);
            $this->positionRepository->save($position);

            $wallet->increaseCapital(
                $position->getCapital()->decrease($oldPositionCapital)
            );
            $this->walletRepository->save($wallet);
        }
    }
}
