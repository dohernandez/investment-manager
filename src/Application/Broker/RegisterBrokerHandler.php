<?php

namespace App\Application\Broker;

use App\Application\Broker\Command\RegisterBroker;
use App\Application\Broker\Repository\BrokerRepositoryInterface;
use App\Domain\Broker\Broker;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RegisterBrokerHandler implements MessageHandlerInterface
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

    public function __invoke(RegisterBroker $message)
    {
        $broker = Broker::register($message->getName(), $message->getSite(), $message->getCurrency());

        $this->brokerRepository->save($broker);

        return $broker;
    }
}
