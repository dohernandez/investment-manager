<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Account\Event\AccountDebited;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AccountDebitedSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        WalletRepositoryInterface $walletRepository,
        LoggerInterface $logger
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->walletRepository = $walletRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AccountDebited::class => ['onAccountDebited', 100],
        ];
    }

    public function onAccountDebited(AccountDebited $event)
    {
        $projectionWallet = $this->projectionWalletRepository->findByAccount($event->getId());

        if (!$projectionWallet) {
            $this->logger->warning('Wallet by account not found', [
                'account_id' => $event->getId(),
            ]);

            return;
        }

        $wallet = $this->walletRepository->find($projectionWallet->getId());

        $wallet->decreaseInvestment($event->getMoney());

        $this->walletRepository->save($wallet);

        $this->logger->debug(
            'Wallet invested decreased',
            [
                'id' => $wallet->getId(),
                'amount' => (string) $event->getMoney(),
            ]
        );
    }
}
