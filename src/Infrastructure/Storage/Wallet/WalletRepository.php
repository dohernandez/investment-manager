<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Wallet;
use App\Domain\Wallet\WalletBook;
use App\Infrastructure\Storage\Repository;

final class WalletRepository extends Repository implements WalletRepositoryInterface
{
    /**
     * @inherent
     */
    protected $dependencies = [
        'book' => WalletBook::class,
    ];

    public function find(string $id): Wallet
    {
        return $this->load(Wallet::class, $id);
    }

    public function save(Wallet $wallet)
    {
        $this->store($wallet);
    }

    public function delete(Wallet $wallet)
    {
        $this->eventSource->saveEvents($wallet->getChanges());

        $this->em->remove($wallet);
        $this->em->flush();
    }
}
