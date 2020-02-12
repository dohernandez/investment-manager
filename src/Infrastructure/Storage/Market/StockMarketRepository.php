<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Domain\Market\StockMarket;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StockMarketRepository implements StockMarketRepositoryInterface
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

    public function find(string $id): StockMarket
    {
        $changes = $this->eventSource->findEvents($id, StockMarket::class);

        $market = (new StockMarket($id))->replay($changes);

        /** @var StockMarket $market */
        $market = $this->em->merge($market);

        return $market;
    }

    public function save(StockMarket $market)
    {
        $this->eventSource->saveEvents($market->getChanges());

        $this->em->persist($market);
        $this->em->flush();
    }
}