<?php

namespace App\Infrastructure\Storage\Report\Hydrate\Wallet;

use App\Domain\Report\Wallet\Wallet;
use App\Domain\Wallet\Wallet as ProjectionWallet;
use App\Infrastructure\Storage\Report\Hydrate\HydrateInterface;

final class WalletHydrate implements HydrateInterface
{
    /**
     * @inheritDoc
     *
     * @param ProjectionWallet $data
     */
    public function hydrate($data)
    {
        if (!$data) {
            return null;
        }

        $book = $data->getBook();

        return new Wallet(
            $data->getId(),
            $data->getName(),
            $data->getSlug(),
            $book->getInvested(),
            $book->getCapital(),
            $book->getNetCapital(),
            $book->getFunds(),
            $book->getDividends() ? $book->getDividends()->getTotal() : null,
            $book->getCommissions() ? $book->getCommissions()->getTotal() : null,
            $book->getConnection() ? $book->getConnection()->getTotal() : null,
            $book->getInterest() ? $book->getInterest()->getTotal() : null,
            $book->getBenefits(),
            $book->getPercentageBenefits()
        );
    }
}
