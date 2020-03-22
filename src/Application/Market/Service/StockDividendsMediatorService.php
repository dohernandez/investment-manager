<?php

namespace App\Application\Market\Service;

use App\Domain\Market\Stock;

use function strtolower;

final class StockDividendsMediatorService implements StockDividendsServiceInterface
{
    /**
     * @var StockDividendsServiceInterface
     */
    private $nasdaqDividendsService;

    /**
     * @var StockDividendsServiceInterface
     */
    private $yahooDividendsService;

    public function __construct(
        StockDividendsServiceInterface $nasdaqDividendsService,
        StockDividendsServiceInterface $yahooDividendsService
    ) {
        $this->nasdaqDividendsService = $nasdaqDividendsService;
        $this->yahooDividendsService = $yahooDividendsService;
    }

    /**
     * @inheritDoc
     */
    public function getStockDividends(Stock $stock): array
    {
        if (strtolower($stock->getMarket()->getCountry()) != 'us') {
            $this->yahooDividendsService->getStockDividends($stock);
        }

        return $this->nasdaqDividendsService->getStockDividends($stock);
    }
}
