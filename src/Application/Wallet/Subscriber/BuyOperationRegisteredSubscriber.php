<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Calculator;
use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionOperationRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\BuyOperationRegistered;
use App\Domain\Wallet\Position;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BuyOperationRegisteredSubscriber implements EventSubscriberInterface
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
     * @var ProjectionOperationRepositoryInterface
     */
    private $projectionOperationRepository;

    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    public function __construct(
        PositionRepositoryInterface $positionRepository,
        ProjectionPositionRepositoryInterface $projectionPositionRepository,
        ProjectionOperationRepositoryInterface $projectionOperationRepository,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->positionRepository = $positionRepository;
        $this->projectionPositionRepository = $projectionPositionRepository;
        $this->projectionOperationRepository = $projectionOperationRepository;
        $this->walletRepository = $walletRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            BuyOperationRegistered::class => ['onBuyOperationRegistered', 100],
        ];
    }

    public function onBuyOperationRegistered(BuyOperationRegistered $event)
    {
        $wallet = $this->walletRepository->find($event->getWallet()->getId());
        $operation = $this->projectionOperationRepository->find($event->getId());

        $projectionPosition = $this->projectionPositionRepository->findByWalletStock(
            $wallet->getId(),
            $event->getStock()->getId(),
            Position::STATUS_OPEN
        );

        if (!$projectionPosition) {
            $position = Position::open(
                $wallet,
                $event->getStock(),
                $event->getDateAt()
            );
        } else {
            $position = $this->positionRepository->find($projectionPosition->getId());
        }

        $position->increasePosition($operation);
        $this->positionRepository->save($position);

        $wallet->updateBuyOperation($operation);
        $this->walletRepository->save($wallet);
    }
}
