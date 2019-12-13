<?php

namespace App\Application\Account\Repository;

use App\Domain\Account\Account;

interface ProjectionAccountRepositoryInterface
{
    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param mixed    $id          The identifier.
     * @param int|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                              or NULL if no specific lock mode should be used
     *                              during the search.
     * @param int|null $lockVersion The lock version.
     *
     * @return Account|null The account instance or NULL if the account can not be found.
     */
    public function find($id, $lockMode = null, $lockVersion = null);

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

