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

        $stockInfo = (new StockInfo($id))->replay($changes);

        /** @var StockInfo $stockInfo */
        $stockInfo = $this->em->merge($stockInfo);

        return $stockInfo;
    }

    public function save(StockInfo $stockInfo)
    {
        if ($stockInfo->getChanges()) {
            $this->eventSource->saveEvents($stockInfo->getChanges());

            $this->em->persist($stockInfo);
            $this->em->flush();
        }
    }
}
