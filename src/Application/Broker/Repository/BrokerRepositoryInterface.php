<?php

namespace App\Application\Broker\Repository;

use App\Domain\Broker\Broker;

interface BrokerRepositoryInterface
{
    public function find(string $id): Broker;

    public function save(Broker $broker);
}
