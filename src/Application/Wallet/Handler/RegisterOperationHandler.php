<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\RegisterOperation;
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

    public function __construct(
        ProjectionWalletRepository $projectionWalletRepository,
        OperationRepositoryInterface $operationRepository
    ) {
        $this->projectionWalletRepository = $projectionWalletRepository;
        $this->operationRepository = $operationRepository;
    }

    public function __invoke(RegisterOperation $message)
    {
        $wallet = $this->projectionWalletRepository->find($message->getWalletId());

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
            $message->getCommission()
        );

        $this->operationRepository->save($operation);

        return $operation;
    }
}
