<?php

namespace App\Application\Account\Repository;

use App\Domain\Account\Account;

interface ProjectionAccountRepositoryInterface
{
    /**
     * Finds an account by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return Account|null The account.
     */
    public function find($id);

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
    public function findAllOpen(): array;

    /**
     * Finds an account by its account number.
     *
     * @param string $accountNo The account number.
     *
     * @return Account|null The account.
     */
    public function findByAccountNo(string $accountNo): ?Account;
}

