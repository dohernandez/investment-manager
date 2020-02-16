<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\PositionRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Domain\Wallet\PositionBook;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Storage\Repository;

final class PositionRepository extends Repository implements PositionRepositoryInterface
{
    /**
     * @inherent
     */
    protected $dependencies = [
        'wallet' => Wallet::class,
        'book'   => PositionBook::class,
    ];

    public function find(string $id): Position
    {
        return $this->load(Position::class, $id);
    }

    public function save(Position $position)
    {
        $this->store($position);
    }

    public function delete(Position $position)
    {
        $this->eventSource->saveEvents($position->getChanges());

        $this->em->remove($position);
        $this->em->flush();
    }
}
