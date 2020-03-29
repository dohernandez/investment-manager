<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Domain\Market\StockPrice;
use App\Infrastructure\Storage\Repository;
use Doctrine\Common\Collections\ArrayCollection;

use function array_merge;

final class StockRepository extends Repository implements StockRepositoryInterface
{
    /**
     * @inherent
     */
    protected $dependencies = [
        'market'        => StockMarket::class,
        'type'          => StockInfo::class,
        'sector'        => StockInfo::class,
        'industry'      => StockInfo::class,
        'price'         => StockPrice::class,
        'nextDividend'  => StockDividend::class,
        'toPayDividend' => StockDividend::class,
    ];

    /**
     * @inherent
     */
    protected $serializeDependencies = [
        'market'        => StockMarket::class,
        'type'          => StockInfo::class,
        'sector'        => StockInfo::class,
        'industry'      => StockInfo::class,
        'price'         => StockPrice::class,
        'nextDividend'  => StockDividend::class,
        'toPayDividend' => StockDividend::class,
        'dividends' => ArrayCollection::class,
    ];

    public function find(string $id): Stock
    {
        return $this->load(Stock::class, $id);
    }

    public function save(Stock $stock)
    {
        $this->store($stock);
    }
}
