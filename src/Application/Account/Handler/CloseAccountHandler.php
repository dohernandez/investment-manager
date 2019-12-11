<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Command\CloseAccount;
use App\Domain\Account\AccountAggregate;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CloseAccountHandler implements MessageHandlerInterface
{
    /**
     * @var EventSourceRepositoryInterface
     */
    private $aggregateRepository;

    public function __construct(EventSourceRepositoryInterface $aggregateRepository)
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
