<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Infrastructure\Storage\Repository;

final class StockRepository extends Repository implements StockRepositoryInterface
{
    public function find(string $id): Stock
    {
        $changes = $this->eventSource->findEvents($id, Stock::class);
        $this->mergeChanges($changes, ['market', 'type', 'sector', 'industry']);

        $stock = (new Stock($id))->replay($changes);

        $stock = $this->em->merge($stock);

        /** @var Stock $stock */
        $stock = $this->em->merge($stock);

        return $stock;
    }

    public function save(Stock $stock)
    {
        if ($stock->getChanges()) {
            $this->eventSource->saveEvents($stock->getChanges());

            $this->em->persist($stock);
            $this->em->flush();
        }
    }
}
