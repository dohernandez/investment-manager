<?php

namespace App\Application\Wallet\Subscriber;

use App\Application\Wallet\Decorator\StockPrevDividendDecoratorInterface;
use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionOperationRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Event\DividendOperationRegistered;
use App\Infrastructure\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DividendOperationRegisteredSubscriber implements EventSubscriberInterface
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

    /**
     * @var StockPrevDividendDecoratorInterface
     */
    private $stockPrevDividendDecorator;

    public function __construct(
        PositionRepositoryInterface $positionRepository,
        ProjectionPositionRepositoryInterface $projectionPositionRepository,
        ProjectionOperationRepositoryInterface $projectionOperationRepository,
        WalletRepositoryInterface $walletRepository,
        StockPrevDividendDecoratorInterface $stockPrevDividendDecorator
    ) {
        $this->positionRepository = $positionRepository;
        $this->projectionPositionRepository = $projectionPositionRepository;
        $this->projectionOperationRepository = $projectionOperationRepository;
        $this->walletRepository = $walletRepository;
        $this->stockPrevDividendDecorator = $stockPrevDividendDecorator;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            DividendOperationRegistered::class => ['onDividendOperationRegistered', 100],
        ];
    }

    public function onDividendOperationRegistered(DividendOperationRegistered $event)
    {
        $wallet = $this->walletRepository->find($event->getWallet()->getId());
        if ($wallet === null) {
            throw new NotFoundException(
                'Wallet not found', [
                                      'id' => $event->getWallet()->getId()
                                  ]
            );
        }

        $operation = $this->projectionOperationRepository->find($event->getId());
        if ($operation === null) {
            throw new NotFoundException('Operation not found', [
                'id' => $event->getId()
            ]);
        }

        $stock = $operation->getStock();
        $this->stockPrevDividendDecorator->decorate($stock, $operation->getDateAt());


        $projectionPosition = $this->projectionPositionRepository->findByWalletStockOpenDateAt(
            $wallet->getId(),
            $stock->getId(),
            $stock->getPrevDividendExDate()
        );
        if ($projectionPosition === null) {
            throw new NotFoundException(
                'Position not found',
                [
                    'walletId' => $wallet->getId(),
                    'stockId'  => $stock->getId(),
                    'ex_date'  => $stock->getPrevDividendExDate()->format('c'),
                ]
            );
        }

        $position = $this->positionRepository->find($projectionPosition->getId());

        $position->increaseDividend($operation);
        $this->positionRepository->save($position);

        $wallet->updateDividendOperation($operation);
        $this->walletRepository->save($wallet);
    }
}
