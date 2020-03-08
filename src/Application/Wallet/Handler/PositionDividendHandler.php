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

        $displayDividendYield = null;
        if ($nextDividend = $position->getStock()->getNextDividend()) {
            $nextDividendYield = $nextDividend->getValue() * 4 / max(
                    $position->getStock()->getPrice()->getValue(),
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

        return new PositionDividend(
            $position->getId(),
            $position->getStock(),
            $position->getInvested(),
            $position->getAmount(),
            $position->getStock()->getNextDividendExDate(),
            $displayDividendYield,
            $realDisplayDividendYield,
            $book->getTotalDividendRetention()
        );
    }
}
