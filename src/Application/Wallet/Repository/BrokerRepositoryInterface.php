<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Broker;

interface BrokerRepositoryInterface
{
    public function find(string $id): ?Broker;

    public function findByName(string $name): ?Broker;
}
