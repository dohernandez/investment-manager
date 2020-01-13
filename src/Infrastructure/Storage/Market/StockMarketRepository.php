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

        $stock = (new StockMarket($id))->replay($changes);

        // manual register tuple loaded into the Entity Manager.
        $this->em->getUnitOfWork()->registerManaged(
            $stock,
            ['id' => $id],
            [
                'id'        => $stock->getId(),
                'currency'  => $stock->getCurrency(),
                'country'   => $stock->getCountry(),
                'symbol'    => $stock->getSymbol(),
                'metadata'  => $stock->getMetadata(),
                'createdAt' => $stock->getCreatedAt(),
                'updatedAt' => $stock->getUpdatedAt(),
            ]
        );

        return $stock;
    }

    public function save(StockMarket $market)
    {
        $this->eventSource->saveEvents($market->getChanges());

        $this->em->getUnitOfWork()->commit($market);
    }
}
