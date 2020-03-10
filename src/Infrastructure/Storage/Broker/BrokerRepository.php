<?php

namespace App\Infrastructure\Storage\Broker;

use App\Application\Broker\Repository\BrokerRepositoryInterface;
use App\Domain\Broker\Broker;
use App\Infrastructure\EventSource\EventSourceRepository;
use App\Infrastructure\Storage\Repository;
use Doctrine\ORM\EntityManagerInterface;

final class BrokerRepository extends Repository implements BrokerRepositoryInterface
{
    public function find(string $id): Broker
    {
        return $this->load(Broker::class, $id);
    }

    public function save(Broker $broker)
    {
        $this->store($broker);
    }
}
