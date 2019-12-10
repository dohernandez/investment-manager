<?php

namespace App\Application\Account\Repository;

use App\Domain\Account\Projection\Account;

interface AccountRepositoryInterface
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

    public function save(Account $account);

    /**
     * @return Account[] The objects.
     */
    public function findAll();

    /**
     * @param mixed    $id          The identifier.
     */
    public function delete($id);
}

