<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Command\CloseAccount;
use App\Domain\Account\AccountAggregate;
use App\Infrastructure\Aggregator\AggregateRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CloseAccountHandler implements MessageHandlerInterface
{
    /**
     * @var AggregateRepositoryInterface
     */
    private $aggregateRepository;

    public function __construct(AggregateRepositoryInterface $aggregateRepository)
    {
        $this->aggregateRepository = $aggregateRepository;
    }

    public function __invoke(CloseAccount $message)
    {
        /** @var AccountAggregate $accountAggregate */
        $accountAggregate = $this->aggregateRepository->load($message->getId(), AccountAggregate::class);

        $accountAggregate->close();

        $this->aggregateRepository->store($accountAggregate);
    }
}
