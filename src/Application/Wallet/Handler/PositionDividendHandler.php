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
            $nextDividendYield = $nextDividend->getValue() * 4 / max(
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
        if ($book->getNextDividendAfterTaxes()) {
            $realDisplayDividendYield = sprintf(
                '%s (%.2f%%)',
                $book->getNextDividendAfterTaxes(),
                $book->getNextDividendYieldAfterTaxes()
            );
        }

        $displayToPayDividendYield = null;
        if ($toPayDividend = $stock->getToPayDividend()) {
            $toPayDividendYield = $toPayDividend->getValue() * 4 / max(
                    $stock->getPrice()->getValue(),
                    1
                ) * 100;
            $displayToPayDividendYield = sprintf(
                '%s (%.2f%%)',
                $toPayDividend,
                $toPayDividendYield
            );
        }

        $realDisplayToPayDividendYield = null;
        if ($book->getNextDividendAfterTaxes()) {
            $realDisplayToPayDividendYield = sprintf(
                '%s (%.2f%%)',
                $book->getNextDividendAfterTaxes(),
                $book->getNextDividendYieldAfterTaxes()
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
