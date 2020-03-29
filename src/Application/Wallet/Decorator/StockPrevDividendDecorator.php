<?php

namespace App\Application\Wallet\Decorator;

use App\Application\Wallet\Repository\StockDividendRepositoryInterface;
use App\Domain\Wallet\Stock;
use DateTime;

final class StockPrevDividendDecorator implements StockPrevDividendDecoratorInterface
{
    /**
     * @var StockDividendRepositoryInterface
     */
    private $stockDividendRepository;

    public function __construct(StockDividendRepositoryInterface $stockDividendRepository)
    {
        $this->stockDividendRepository = $stockDividendRepository;
    }

    public function decorate(Stock &$stock, DateTime $date)
    {
        $dividend = $this->stockDividendRepository->findLastBeforeExDateByStock(
            $stock->getId(),
            $date
        );

        if (!$dividend) {
            return null;
        }

        $stock = $stock->changePrevDividendExDate($dividend->getExDate());
    }
}
