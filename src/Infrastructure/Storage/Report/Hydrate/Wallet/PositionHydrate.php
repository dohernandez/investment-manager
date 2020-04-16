<?php

namespace App\Infrastructure\Storage\Report\Hydrate\Wallet;

use App\Domain\Report\Wallet\Position;
use App\Domain\Report\Wallet\UpDown;
use App\Domain\Wallet\Position as ProjectionPosition;
use App\Infrastructure\Storage\Report\Hydrate\HydrateInterface;

final class PositionHydrate implements HydrateInterface
{
    /**
     * @var StockHydrate
     */
    private $stockHydrate;

    /**
     * @var DividendHydrate
     */
    private $dividendHydrate;

    public function __construct(StockHydrate $stockHydrate, DividendHydrate $dividendHydrate)
    {
        $this->stockHydrate = $stockHydrate;
        $this->dividendHydrate = $dividendHydrate;
    }

    /**
     * @inheritDoc
     *
     * @param ProjectionPosition $data
     */
    public function hydrate($data)
    {
        if (!$data) {
            return null;
        }

        $book = $data->getBook();
        $stock = $data->getStock();

        $nextDividend = $this->dividendHydrate->hydrate(
            [
                'dividend'          => $stock->getNextDividend(),
                'dividendYield'     => $stock->getNextYearDividend() ?
                    $stock->getNextYearDividend()->getValue() / max(
                        $stock->getPrice() ? $stock->getPrice()->getValue() : 0,
                        1
                    ) * 100 :
                    null,
                'realDividend'      => $book->getNextDividendAfterTaxes(),
                'realDividendYield' => $book->getNextDividendYieldAfterTaxes(),
            ]
        );

        $toPayDividend = $this->dividendHydrate->hydrate(
            [
                'dividend'          => $book->getToPayDividend(),
                'dividendYield'     => $book->getToPayDividendYield(),
                'realDividend'      => $book->getToPayDividendAfterTaxes(),
                'realDividendYield' => $book->getToPayDividendYieldAfterTaxes(),
            ]
        );

        $percentageBenefits = $book->getPercentageBenefits();
        $direction = $percentageBenefits === 0 ?
            UpDown::DIRECTION_LEFT :
            $percentageBenefits > 0 ? UpDown::DIRECTION_UP : UpDown::DIRECTION_DOWN;
        $upDownBenefits = new UpDown($percentageBenefits, $direction);

        $percentageChanged = $book->getPercentageChanged();
        $direction = $percentageChanged === 0 ?
            UpDown::DIRECTION_LEFT :
            $percentageChanged > 0 ? UpDown::DIRECTION_UP : UpDown::DIRECTION_DOWN;
        $upDownChanges = new UpDown($percentageChanged, $direction);

        return new Position(
            $data->getOpenedAt(),
            $this->stockHydrate->hydrate($stock),
            $data->getAmount(),
            $data->getCapital(),
            $data->getInvested(),
            $book->getDividendPaid() ? $book->getDividendPaid()->getTotal() : null,
            $upDownBenefits,
            $upDownChanges,
            $nextDividend,
            $toPayDividend
        );
    }
}
