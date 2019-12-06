<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Event\AccountUpdated;
use App\Application\Account\Command\WithdrawMoneyCommand;
use App\Domain\Account\AccountAggregate;
use App\Infrastructure\Aggregator\AggregateRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class WithdrawMoneyCommandHandler implements MessageHandlerInterface
{
    /**
     * @var AggregateRepositoryInterface
     */
    private $aggregateRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(AggregateRepositoryInterface $aggregateRepository, MessageBusInterface $bus)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->bus = $bus;
    }

    public function __invoke(WithdrawMoneyCommand $message)
    {
        /** @var AccountAggregate $accountAggregate */
        $accountAggregate = $this->aggregateRepository->load($message->getId(), AccountAggregate::class);

        $accountAggregate->withdraw($message->getMoney());

        $this->aggregateRepository->store($accountAggregate);

        $this->bus->dispatch(
            new AccountUpdated(
                $accountAggregate->getId(),
                $accountAggregate->getBalance(),
                $accountAggregate->getUpdatedAt()
            )
        );
    }
}
