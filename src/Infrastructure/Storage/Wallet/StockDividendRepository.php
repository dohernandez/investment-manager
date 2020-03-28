<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Market\Repository\StockDividendRepositoryInterface as ProjectionStockDividendRepositoryInterface;
use App\Application\Wallet\Repository\StockDividendRepositoryInterface;
use App\Domain\Market\Stock as ProjectionStock;
use App\Domain\Market\StockDividend as ProjectionStockDividendAlias;
use App\Domain\Wallet\StockDividend;
use DateTime;

final class StockDividendRepository implements StockDividendRepositoryInterface
{

    /**
     * @var ProjectionStockDividendRepositoryInterface
     */
    private $stockDividendRepository;

    public function __construct(ProjectionStockDividendRepositoryInterface $stockDividendRepository)
    {
        $this->stockDividendRepository = $stockDividendRepository;
    }

    public function findLastBeforeDateByStock(string $id, DateTime $date): ?StockDividend
    {
        $dividend = $this->stockDividendRepository->findLastBeforeDateByStock(
            new ProjectionStock($id),
            $date
        );

        return $this->hydrate($dividend);
    }

    private function hydrate(?ProjectionStockDividendAlias $dividend): ?StockDividend
    {
        if (!$dividend) {
            return null;
        }

        return new StockDividend(
            $dividend->getExDate(),
            $dividend->getValue()
        );
    }
}
