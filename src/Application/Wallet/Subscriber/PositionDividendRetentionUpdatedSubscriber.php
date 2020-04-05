<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Decorator\WalletPositionDecorateInterface;
use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\PositionDividendRetentionUpdated;
use App\Infrastructure\Exception\NotFoundException;
use DateTime;
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

    /**
     * @var WalletPositionDecorateInterface
     */
    private $positionDecorate;

    public function __construct(
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        WalletRepositoryInterface $walletRepository,
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository,
        WalletPositionDecorateInterface $positionDecorate
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->walletRepository = $walletRepository;
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
        $this->positionDecorate = $positionDecorate;
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
        $wallet = $this->projectionWalletRepository->findByPosition($event->getId());
        if ($wallet === null) {
            throw new NotFoundException(
                'Wallet not found',
                [
                    'position.id' => $event->getId(),
                ]
            );
        }

        $wallet = $this->walletRepository->find($wallet->getId());
        $this->positionDecorate->decorate($wallet);

        $exchangeMoneyRates = $this->exchangeMoneyRepository->findAllByToCurrency($wallet->getCurrency());

        $wallet->reCalculateDividendProjectedFromDate(new DateTime('now'), $exchangeMoneyRates);
        $this->walletRepository->save($wallet);
    }
}
