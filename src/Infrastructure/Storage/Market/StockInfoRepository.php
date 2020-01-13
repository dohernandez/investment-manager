<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Domain\Market\StockInfo;
use App\Infrastructure\EventSource\EventSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StockInfoRepository implements StockInfoRepositoryInterface
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

    public function find(string $id): StockInfo
    {
        $changes = $this->eventSource->findEvents($id, StockInfo::class);

        $stock = (new StockInfo($id))->replay($changes);

        // manual register tuple loaded into the Entity Manager.
        $this->em->getUnitOfWork()->registerManaged(
            $stock,
            ['id' => $id],
            [
                'id'        => $stock->getId(),
                'name'      => $stock->getName(),
                'type'      => $stock->getType(),
                'createdAt' => $stock->getCreatedAt(),
            ]
        );

        return $stock;
    }

    public function save(StockInfo $stockInfo)
    {
        $this->eventSource->saveEvents($stockInfo->getChanges());

        $this->em->getUnitOfWork()->commit($stockInfo);
    }
}
