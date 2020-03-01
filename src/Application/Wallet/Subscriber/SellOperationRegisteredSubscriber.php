<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Calculator;
use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionOperationRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\SellOperationRegistered;
use App\Domain\Wallet\Position;
use App\Infrastructure\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SellOperationRegisteredSubscriber implements EventSubscriberInterface
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
            SellOperationRegistered::class => ['onSellOperationRegistered', 100],
        ];
    }

    public function onSellOperationRegistered(SellOperationRegistered $event)
    {
        $wallet = $this->walletRepository->find($event->getWallet()->getId());

        if ($wallet === null) {
            throw new NotFoundException('Wallet not found', [
                'id' => $event->getWallet()->getId()
            ]);
        }

        $operation = $this->projectionOperationRepository->find($event->getId());

        if ($wallet === null) {
            throw new NotFoundException('Operation not found', [
                'id' => $event->getId()
            ]);
        }

        $projectionPosition = $this->projectionPositionRepository->findByStock(
            $wallet->getId(),
            $event->getStock()->getId(),
            Position::STATUS_OPEN
        );

        if ($wallet === null) {
            throw new NotFoundException('Operation not found', [
                'walletId' => $wallet->getId(),
                'stockId' => $event->getStock()->getId(),
            ]);
        }

        $position = $this->positionRepository->find($projectionPosition->getId());

        $position->decreasePosition($operation);
        $this->positionRepository->save($position);

        $wallet->updateSellOperation($operation);
        $this->walletRepository->save($wallet);
    }
}
