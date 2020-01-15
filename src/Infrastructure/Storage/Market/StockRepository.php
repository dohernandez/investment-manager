<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class StockRepository implements StockRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventSourceRepositoryInterface
     */
    private $eventSource;

    public function __construct(EntityManagerInterface $em, EventSourceRepositoryInterface $eventSource)
    {
        $this->em = $em;
        $this->eventSource = $eventSource;
    }

    public function find(string $id): Stock
    {
        $changes = $this->eventSource->findEvents($id, Stock::class);

        $stock = (new Stock($id))->replay($changes);

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
