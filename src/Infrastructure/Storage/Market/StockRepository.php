<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StockRepository implements StockRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventSourceRepository
     */
    private $eventSource;

    public function __construct(EntityManagerInterface $em, EventSourceRepository $eventSource)
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
        $this->eventSource->saveEvents($stock->getChanges());

        $this->em->persist($stock);
        $this->em->flush();
    }
}
