<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\PositionDividendRetentionUpdated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PositionDividendRetentionUpdatedSubscriber implements EventSubscriberInterface
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
     * @var ExchangeMoneyRepositoryInterface
     */
    private $exchangeMoneyRepository;

    public function __construct(
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        WalletRepositoryInterface $walletRepository,
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->walletRepository = $walletRepository;
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PositionDividendRetentionUpdated::class => ['onPositionDividendRetentionUpdated', 100],
        ];
    }

    public function onPositionDividendRetentionUpdated(PositionDividendRetentionUpdated $event)
    {
        $wallets = $this->projectionWalletRepository->findAllByStockOnOpenPosition($event->getId());

        \dump($wallets);
        foreach ($wallets as $wallet) {
            $wallet = $this->walletRepository->find($wallet->getId());
            $exchangeMoneyRates = $this->exchangeMoneyRepository->findAllByToCurrency($wallet->getCurrency());

            $wallet->updateDividendProjected($exchangeMoneyRates);
            $this->walletRepository->save($wallet);
        }
    }
}
