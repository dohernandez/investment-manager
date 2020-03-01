<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Account\Event\AccountCredited;
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

    public function __construct(
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->walletRepository = $walletRepository;
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

        $wallet = $this->walletRepository->find($projectionWallet->getId());

        $wallet->increaseInvestment($event->getMoney());

        $this->walletRepository->save($wallet);
    }
}
