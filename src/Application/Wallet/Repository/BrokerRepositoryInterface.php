<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Broker;

interface BrokerRepositoryInterface
{
    public function find(string $id): Broker;
}
