<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\ExchangeMoney\Event\MoneyRatesUpdated;
use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Domain\Wallet\Rate;
use App\Infrastructure\Exception\NotFoundException;
use App\Infrastructure\Money\Money;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MoneyRateUpdatedSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var PositionRepositoryInterface
     */
    private $positionRepository;

    public function __construct(
        ProjectionWalletRepositoryInterface $projectionWalletRepository,
        ProjectionPositionRepositoryInterface $projectionPositionRepository,
        WalletRepositoryInterface $walletRepository,
        PositionRepositoryInterface $positionRepository
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->projectionPositionRepository = $projectionPositionRepository;
        $this->walletRepository = $walletRepository;
        $this->positionRepository = $positionRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            MoneyRatesUpdated::class => ['onMoneyRatesUpdated', 100],
        ];
    }

    public function onMoneyRatesUpdated(MoneyRatesUpdated $event)
    {
        $wallets = $this->projectionWalletRepository->findAll();

        $moneyExchangeRates = [];
        foreach ($event->getMoneyExchangeRates() as $moneyExchangeRate) {
            $moneyExchangeRates[$moneyExchangeRate->getToCurrency()->getCurrencyCode()] = new Rate(
                $moneyExchangeRate->getToCurrency(),
                $moneyExchangeRate->getFromCurrency(),
                $moneyExchangeRate->getRate()
            );
        }

        foreach ($wallets as $wallet) {
            $capital = new Money($wallet->getCurrency());

            $wallet = $this->walletRepository->find($wallet->getId());
            if ($wallet === null) {
                throw new NotFoundException(
                    'Wallet not found',
                    [
                        'id' => $wallet->getId()
                    ]
                );
            }

            $projectionPositions = $this->projectionPositionRepository->findAllByWalletStatus(
                $wallet->getId(),
                Position::STATUS_OPEN
            );

            foreach ($projectionPositions as $projectionPosition) {
                $position = $this->positionRepository->find($projectionPosition->getId());
                if (!$position) {
                    throw new NotFoundException(
                        'Position not found',
                        [
                            'id'       => $projectionPosition->getId(),
                            'walletId' => $wallet->getId(),
                            'stockId'  => $event->getId(),
                            'status'   => Position::STATUS_OPEN,
                        ]
                    );
                }

                $previousPositionCapital = $position->getCapital();

                $position->updateMoneyRate($moneyExchangeRates[$position->getStock()->getCurrency()->getCurrencyCode()]);
                $this->positionRepository->save($position);

                $capital = $capital->increase(
                    $position->getCapital()->decrease($previousPositionCapital)
                );
            }

            $wallet->increaseCapital($capital);
            $this->walletRepository->save($wallet);
        }
    }
}
