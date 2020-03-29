<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Handler\Output\PositionDividend;
use App\Domain\Wallet\Position;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

abstract class PositionDividendHandler  implements MessageHandlerInterface
{
    protected function createPositionDividend(Position $position): PositionDividend
    {
        $book = $position->getBook();
        $stock = $position->getStock();

        $displayDividendYield = null;
        if ($nextDividend = $stock->getNextDividend()) {
            $nextDividendYield = $stock->getNextYearDividend()->getValue() / max(
                    $stock->getPrice()->getValue(),
                    1
                ) * 100;

            $displayDividendYield = sprintf(
                '%s (%.2f%%)',
                $nextDividend,
                $nextDividendYield
            );
        }

        $realDisplayDividendYield = null;
        if ($realDividendYield = $book->getNextDividendAfterTaxes()) {
            $realDisplayDividendYield = sprintf(
                '%s (%.2f%%)',
                $realDividendYield,
                $book->getNextDividendYieldAfterTaxes()
            );
        }

        $displayToPayDividendYield = null;
        if ($toPayDividend = $book->getToPayDividend()) {
            $displayToPayDividendYield = sprintf(
                '%s (%.2f%%)',
                $toPayDividend,
                $book->getToPayDividendYield()
            );
        }

        $realDisplayToPayDividendYield = null;
        if ($realToPayDividendYield = $book->getToPayDividendAfterTaxes()) {
            $realDisplayToPayDividendYield = sprintf(
                '%s (%.2f%%)',
                $realToPayDividendYield,
                $book->getToPayDividendYieldAfterTaxes()
            );
        }

        return new PositionDividend(
            $position->getId(),
            $stock,
            $position->getInvested(),
            $position->getAmount(),
            $stock->getNextDividendExDate(),
            $displayDividendYield,
            $realDisplayDividendYield,
            $stock->getToPayDividendDate(),
            $displayToPayDividendYield,
            $realDisplayToPayDividendYield,
            $book->getTotalDividendRetention()
        );
    }
}
