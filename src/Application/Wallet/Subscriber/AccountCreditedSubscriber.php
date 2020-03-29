<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Account\Event\AccountCredited;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AccountCreditedSubscriber implements EventSubscriberInterface
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
            AccountCredited::class => ['onAccountCredited', 100],
        ];
    }

    public function onAccountCredited(AccountCredited $event)
    {
        $projectionWallet = $this->projectionWalletRepository->findByAccount($event->getId());

        if (!$projectionWallet) {
            $this->logger->debug('Wallet by account not found. Skipped', [
                'account_id' => $event->getId(),
            ]);

            return;
        }

        $wallet = $this->walletRepository->find($projectionWallet->getId());

        $wallet->increaseInvestment($event->getMoney());

        $this->walletRepository->save($wallet);

        $this->logger->debug(
            'Wallet invested increased',
            [
                'id' => $wallet->getId(),
                'amount' => (string) $event->getMoney(),
            ]
        );
    }
}
