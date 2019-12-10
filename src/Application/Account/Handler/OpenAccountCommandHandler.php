<?php

namespace App\Application\Account\Handler;

use App\Domain\Account\AccountAggregate;
use App\Application\Account\Event\AccountCreated;
use App\Application\Account\Command\OpenAccountCommand;
use App\Infrastructure\Aggregator\AggregateRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class OpenAccountCommandHandler implements MessageHandlerInterface
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

    public function __invoke(OpenAccountCommand $message)
    {
        $accountAggregate = AccountAggregate::open(
            $message->getName(),
            $message->getType(),
            $message->getAccountNo(),
            $message->getCurrency()
        );

        $this->aggregateRepository->store($accountAggregate);

        $this->bus->dispatch(
            new AccountCreated(
                $accountAggregate->getId(),
                $accountAggregate->getName(),
                $accountAggregate->getType(),
                $accountAggregate->getAccountNo(),
                $accountAggregate->getBalance(),
                $accountAggregate->getCreatedAt()
            )
        );

        return $accountAggregate;
    }
}
