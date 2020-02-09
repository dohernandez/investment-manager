<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Broker\Repository\ProjectionBrokerRepositoryInterface;
use App\Application\Wallet\Repository\BrokerRepositoryInterface;
use App\Domain\Broker\Broker as ProjectionBroker;
use App\Domain\Wallet\Broker;

final class BrokerRepository implements BrokerRepositoryInterface
{
    /**
     * @var ProjectionBrokerRepositoryInterface
     */
    private $projectionBrokerRepository;

    public function __construct(ProjectionBrokerRepositoryInterface $projectionBrokerRepository)
    {
        $this->projectionBrokerRepository = $projectionBrokerRepository;
    }

    public function find(string $id): Broker
    {
        $projectionAccount = $this->projectionBrokerRepository->find($id);

        return $this->hydrate($projectionAccount);
    }

    public function hydrate(ProjectionBroker $projectionBroker): Broker
    {
        return new Broker(
            $projectionBroker->getId(),
            $projectionBroker->getName(),
            $projectionBroker->getCurrency()
        );
    }
}
