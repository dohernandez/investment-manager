<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionOperationRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\InterestOperationRegistered;
use App\Infrastructure\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InterestOperationRegisteredSubscriber implements EventSubscriberInterface
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
            InterestOperationRegistered::class => ['onInterestOperationRegistered', 100],
        ];
    }

    public function onInterestOperationRegistered(InterestOperationRegistered $event)
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

        $wallet->increaseInterest($operation);
        $this->walletRepository->save($wallet);
    }
}
