<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\GetPositionDividends;
use App\Application\Wallet\Handler\Output\PositionDividend;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetPositionDividendsHandler implements MessageHandlerInterface
{
    /**
     * @var ProjectionPositionRepositoryInterface
     */
    private $projectionPositionRepository;

    public function __construct(ProjectionPositionRepositoryInterface $projectionPositionRepository)
    {
        $this->projectionPositionRepository = $projectionPositionRepository;
    }

    public function __invoke(GetPositionDividends $message)
    {
        if ($message->getPositionStatus()) {
            $positions = $this->projectionPositionRepository->findAllByWalletStatus(
                $message->getWalletId(),
                $message->getPositionStatus()
            );
        } else {
            $positions = $this->projectionPositionRepository->findAllByWallet($message->getWalletId());
        }

        $positionDividends = [];
        foreach ($positions as $position) {
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

            $positionDividends[] = new PositionDividend(
                $position->getId(),
                $position->getStock(),
                $position->getInvested(),
                $position->getAmount(),
                $position->getStock()->getNextDividendExDate(),
                $displayDividendYield,
                $realDisplayDividendYield
            );
        }

        return $positionDividends;
    }
}
