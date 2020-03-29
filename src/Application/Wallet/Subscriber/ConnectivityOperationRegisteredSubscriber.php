<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionOperationRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\ConnectivityOperationRegistered;
use App\Infrastructure\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConnectivityOperationRegisteredSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProjectionOperationRepositoryInterface
     */
    private $projectionOperationRepository;

    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    public function __construct(
        ProjectionOperationRepositoryInterface $projectionOperationRepository,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->projectionOperationRepository = $projectionOperationRepository;
        $this->walletRepository = $walletRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ConnectivityOperationRegistered::class => ['onConnectivityOperationRegistered', 100],
        ];
    }

    public function onConnectivityOperationRegistered(ConnectivityOperationRegistered $event)
    {
        $wallet = $this->walletRepository->find($event->getWallet()->getId());
        if ($wallet === null) {
            throw new NotFoundException('Wallet not found', [
                'id' => $event->getWallet()->getId()
            ]);
        }

        $operation = $this->projectionOperationRepository->find($event->getId());
        if ($operation === null) {
            throw new NotFoundException('Operation not found', [
                'id' => $event->getId()
            ]);
        }

        $wallet->increaseConnectivity($operation);
        $this->walletRepository->save($wallet);
    }
}
