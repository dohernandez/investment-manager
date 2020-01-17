<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Domain\Market\StockPrice;
use App\Infrastructure\Storage\Repository;
use Doctrine\ORM\Query\Expr;
use ReflectionClass;

use function dump;

final class StockRepository extends Repository implements StockRepositoryInterface
{
    /**
     * @inherent
     */
    protected $dependencies = [
        'market' => StockMarket::class,
        'type' => StockInfo::class,
        'sector' => StockInfo::class,
        'industry' => StockInfo::class,
        'price' => StockPrice::class,
    ];

    public function find(string $id): Stock
    {
        return $this->load(Stock::class, $id);
    }

    public function save(Stock $stock)
    {
        $linkPrice = false;

        if ($stock->getPrice() && $stock->getPrice()->getId() === null) {
            $linkPrice = true;
        }

        $this->store($stock);

        if ($linkPrice) {
            $stock->linkPrice();

            // save only the new change
            $changes = $stock->getChanges();
            $this->unburdenDependencies($changes);
            $this->eventSource->saveEvents($changes);
            $this->em->flush();
        }
    }
}
