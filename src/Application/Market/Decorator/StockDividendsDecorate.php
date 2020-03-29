<?php

namespace App\Application\Market\Decorator;

use App\Application\Market\Repository\StockDividendRepositoryInterface;
use App\Domain\Market\Stock;
use Doctrine\Common\Collections\ArrayCollection;

class StockDividendsDecorate implements StockDividendsDecorateInterface
{
    /**
     * @var StockDividendRepositoryInterface
     */
    private $stockDividendRepository;

    public function __construct(StockDividendRepositoryInterface $stockDividendRepository)
    {
        $this->stockDividendRepository = $stockDividendRepository;
    }

    public function decorate(Stock $stock)
    {
        if (!$stock->getId()) {
            return;
        }

        $dividends = $this->stockDividendRepository->findAllByStock($stock);

        $stock->setDividends(new ArrayCollection($dividends));
    }
}
