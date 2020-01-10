<?php

namespace App\Application\Broker\Handler;

use App\Application\Broker\Command\ChangeBroker;
use App\Application\Broker\Repository\BrokerRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ChangeBrokerHandler implements MessageHandlerInterface
{
    /**
     * @var BrokerRepositoryInterface
     */
    private $brokerRepository;

    public function __construct(
        BrokerRepositoryInterface $brokerRepository
    ) {
        $this->brokerRepository = $brokerRepository;
    }

    public function __invoke(ChangeBroker $message)
    {
        $broker = $this->brokerRepository->find($message->getId());

        $broker->change($message->getName(), $message->getSite(), $message->getCurrency());

        $this->brokerRepository->save($broker);

        return $broker;
    }
}
