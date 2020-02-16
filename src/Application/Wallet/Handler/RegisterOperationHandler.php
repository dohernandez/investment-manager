<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\RegisterOperation;
use App\Application\Wallet\Repository\ExchangeMoneyRepositoryInterface;
use App\Application\Wallet\Repository\OperationRepositoryInterface;
use App\Domain\Wallet\Operation;
use App\Infrastructure\Storage\Wallet\ProjectionWalletRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RegisterOperationHandler implements MessageHandlerInterface
{
    /**
     * @var ProjectionWalletRepository
     */
    private $projectionWalletRepository;

    /**
     * @var OperationRepositoryInterface
     */
    private $operationRepository;

    /**
     * @var ExchangeMoneyRepositoryInterface
     */
    private $exchangeMoneyRepository;

    public function __construct(
        ProjectionWalletRepository $projectionWalletRepository,
        OperationRepositoryInterface $operationRepository,
        ExchangeMoneyRepositoryInterface $exchangeMoneyRepository
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->operationRepository = $operationRepository;
        $this->exchangeMoneyRepository = $exchangeMoneyRepository;
    }

    public function __invoke(RegisterOperation $message)
    {
        $wallet = $this->projectionWalletRepository->find($message->getWalletId());

        $exchangeRate = $this->exchangeMoneyRepository->find($message->getStock()->getCurrency(), $wallet->getCurrency());

        $operation = Operation::register(
            $wallet,
            $message->getDateAt(),
            $message->getType(),
            $message->getValue(),
            $message->getStock(),
            $message->getAmount(),
            $message->getPrice(),
            $message->getPriceChange(),
            $message->getPriceChangeCommission(),
            $message->getCommission(),
            $exchangeRate
        );

        $this->operationRepository->save($operation);

        return $operation;
    }
}
