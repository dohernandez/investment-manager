<?php

namespace App\Infrastructure\Storage\Report\Hydrate\Wallet;

use App\Domain\Report\Wallet\Stock;
use App\Domain\Wallet\Stock as ProjectionStock;
use App\Infrastructure\Storage\Report\Hydrate\HydrateInterface;

final class StockHydrate implements HydrateInterface
{
    /**
     * @inheritDoc
     *
     * @param ProjectionStock $data
     */
    public function hydrate($data)
    {
        if (!$data) {
            return null;
        }

        return new Stock(
            $data->getName(),
            $data->getSymbol(),
            $data->getPrice()
        );
    }
}
