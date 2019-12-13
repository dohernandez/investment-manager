<?php

namespace App\Application\Account\Repository;

use App\Domain\Account\Account;

interface ProjectionAccountRepositoryInterface
{
    /**
     * @param string $query
     * @param int $limit
     *
     * @return Account[] The objects.
     */
    public function findAllOpenMatching(string $query, int $limit = 5): array;

    /**
     * @return Account[] The objects.
     */
    public function findAllOpen();
}

