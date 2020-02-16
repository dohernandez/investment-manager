<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\GetWalletStatistics;
use App\Application\Wallet\Handler\Output\WalletStatistics;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetWalletStatisticsHandler implements MessageHandlerInterface
{
    /**
     * @var ProjectionWalletRepositoryInterface
     */
    private $projectionWalletRepository;

    public function __construct(ProjectionWalletRepositoryInterface $projectionWalletRepository)
    {
        $this->projectionWalletRepository = $projectionWalletRepository;
    }

    public function __invoke(GetWalletStatistics $message)
    {
        $wallet = $this->projectionWalletRepository->find($message->getId());

        $book = $wallet->getBook();

        return new WalletStatistics(
            $book->getInvested(),
            $book->getCapital(),
            $book->getCapital()->increase($book->getFunds()),
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
