<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Decorator\WalletPositionDecorateInterface;
use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\WalletBuyOperationUpdated;
use App\Domain\Wallet\Event\WalletSellOperationUpdated;
use App\Infrastructure\Context\Context;
use App\Infrastructure\Context\Logger;
use App\Infrastructure\Exception\NotFoundException;
use DateTime;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class WalletBuySellOperationUpdatedSubscriber implements EventSubscriberInterface
{
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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository,
        WalletPositionDecorateInterface $positionDecorate,
        LoggerInterface $logger
    ) {
        $this->walletRepository = $walletRepository;
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
        $this->positionDecorate = $positionDecorate;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            WalletBuyOperationUpdated::class => ['onWalletBuySellOperationUpdated', 100],
            WalletSellOperationUpdated::class => ['onWalletBuySellOperationUpdated', 100],
        ];
    }

    /**
     * @param WalletBuyOperationUpdated|WalletSellOperationUpdated $event
     */
    public function onWalletBuySellOperationUpdated($event)
    {
        if (!$event instanceof WalletBuyOperationUpdated && !$event instanceof WalletSellOperationUpdated) {
            throw new InvalidArgumentException('event not supported');
        }

        $wallet = $this->walletRepository->find($event->getId());
        if ($wallet === null) {
            throw new NotFoundException(
                'Wallet not found',
                [
                    'id' => $event->getId(),
                ]
            );
        }

        $this->positionDecorate->decorate($wallet);

        $exchangeMoneyRates = $this->exchangeMoneyRepository->findAllByToCurrency($wallet->getCurrency());

        $context = Logger::toContext(Context::TODO(), $this->logger);
        $wallet->reCalculateDividendProjectedFromDate($context, new DateTime(), $exchangeMoneyRates);
        $this->walletRepository->save($wallet);
    }
}
