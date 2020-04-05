<?php

namespace App\Application\Wallet\Repository;

use App\Domain\Wallet\Position;
use DateTime;

interface ProjectionPositionRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return Position|null The position.
     */
    public function find($id);

    public function findByWalletStock(string $walletId, string $stockId, ?string $status = null): ?Position;

    /**
     * @param string $walletId
     *
     * @return Position[]
     */
    public function findAllByWallet(string $walletId): array;

    /**
     * @param string $walletId
     * @param string $status
     *
     * @return Position[]
     */
    public function findAllByWalletStatus(string $walletId, string $status): array;

    public function findByWalletStockOpenDateAt(string $walletId, string $stockId, DateTime $datedAt): ?Position;

    public function findByWalletAndStatus(string $id, string $walletId, string $status): ?Position;

    /**
     * @param string $walletId
     * @param int $year
     *
     * @return Position[]
     */
    public function findAllOpenOrHasOpenedByWalletInYear(string $walletId, int $year): array;
}
