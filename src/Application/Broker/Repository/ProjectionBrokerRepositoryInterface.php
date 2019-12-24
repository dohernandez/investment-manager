<?php

namespace App\Application\Broker\Repository;

use App\Domain\Broker\Broker;

interface ProjectionBrokerRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return Broker|null The broker.
     */
    public function find($id);

    /**
     * @return Broker[] The objects.
     */
    public function findAll();
}

