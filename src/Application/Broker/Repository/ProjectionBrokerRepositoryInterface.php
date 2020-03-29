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
     * @param string $query
     * @param int $limit
     *
     * @return Broker[]
     */
    public function findAllMatching(string $query, int $limit = 5): array;

    public function findByName(string $name): ?Broker;
}

