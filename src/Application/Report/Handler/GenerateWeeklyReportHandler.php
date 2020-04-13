<?php

namespace App\Application\Report\Handler;

use App\Application\Report\Command\GenerateWeeklyReport;
use App\Application\Report\Repository\WeeklyStockRepositoryInterface;
use App\Application\Report\Repository\WeeklyWalletRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class GenerateWeeklyReportHandler
{
    /**
     * @var WeeklyWalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var WeeklyStockRepositoryInterface
     */
    private $stockRepository;

    public function __construct(
        WeeklyWalletRepositoryInterface $walletRepository,
        WeeklyStockRepositoryInterface $stockRepository
    ) {
        $this->walletRepository = $walletRepository;
        $this->stockRepository = $stockRepository;
    }

    public function __invoke(GenerateWeeklyReport $message)
    {
    }
}
