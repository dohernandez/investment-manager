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

        // manual register tuple loaded into the Entity Manager.
        $this->em->getUnitOfWork()->registerManaged(
            $stock,
            ['id' => $id],
            [
                'id'                => $stock->getId(),
                'name'              => $stock->getName(),
                'symbol'            => $stock->getSymbol(),
                'market'            => $stock->getMarket(),
                'value'             => $stock->getValue(),
                'description'       => $stock->getDescription(),
                'type'              => $stock->getType(),
                'sector'            => $stock->getSector(),
                'industry'          => $stock->getIndustry(),
                'notes'             => $stock->getNotes(),
                'metadata'          => $stock->getMetadata(),
                'nextDividend'      => $stock->getNextDividend(),
                'toPayDividend'     => $stock->getToPayDividend(),
                'createdAt'         => $stock->getCreatedAt(),
                'updatedAt'         => $stock->getUpdatedAt(),
                'updatedPriceAt'    => $stock->getUpdatedPriceAt(),
                'updatedDividendAt' => $stock->getUpdatedDividendAt(),
            ]
        );

        return $stock;
    }

    public function save(Stock $stock)
    {
        $this->eventSource->saveEvents($stock->getChanges());

        $this->em->persist($stock);
        $this->em->flush();
    }
}
