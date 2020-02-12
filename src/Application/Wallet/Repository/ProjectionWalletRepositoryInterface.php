<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Wallet;

interface ProjectionWalletRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return Wallet|null The broker.
     */
    public function find($id);

    public function findBySlug(string $slug): ?Wallet;

    /**
     * @return Wallet[] The objects.
     */
    public function findAll();

    public function findByAccount(string $accountId): ?Wallet;
}
