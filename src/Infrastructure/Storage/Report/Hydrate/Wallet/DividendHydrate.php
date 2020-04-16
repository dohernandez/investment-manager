<?php

namespace App\Infrastructure\Storage\Report\Hydrate\Wallet;

use App\Domain\Report\Wallet\Dividend;
use App\Domain\Report\Wallet\Wallet;
use App\Domain\Wallet\PositionBook;
use App\Domain\Wallet\Stock;
use App\Domain\Wallet\Wallet as ProjectionWallet;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Storage\Report\Hydrate\HydrateInterface;

final class DividendHydrate implements HydrateInterface
{
    /**
     * @inheritDoc
     *
     * @param ProjectionWallet $data
     */
    public function hydrate($data)
    {
        if (!$data || empty($data)) {
            return null;
        }

        $exDate = !empty($data['exDate']) ? $data['exDate'] : null;

        $dividendYield = null;
        if (!empty($data['dividend'])) {
            $dividendYield = sprintf(
                '%s (%.2f%%)',
                $data['dividend'],
                $data['dividendYield']
            );
        }

        $realDividendYield = null;
        if (!empty($data['realDividend'])) {
            $realDividendYield = sprintf(
                '%s (%.2f%%)',
                $data['realDividend'],
                $data['realDividendYield']
            );
        }

        return new Dividend($dividendYield, $exDate, $realDividendYield);
    }
}
