<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Stock;
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

    /**
     * @param string $stockId
     *
     * @return Wallet[]
     */
    public function findAllByStockInOpenPosition(string $stockId): array;

    /**
     * @param string $slug
     *
     * @return Stock[]
     */
    public function findAllStocksInWalletOnOpenPositionBySlug(string $slug): array;

    public function findAllMatching(string $nameOrSlug): array;
}
