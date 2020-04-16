<?php

namespace App\Application\Report\Handler;

use App\Application\Report\Command\GenerateWeeklyReport;
use App\Application\Report\Repository\StockRepositoryInterface;
use App\Application\Report\Repository\WeeklyWalletRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class GenerateWeeklyReportHandler
{
    /**
     * @var WeeklyWalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    public function __construct(
        WeeklyWalletRepositoryInterface $walletRepository,
        StockRepositoryInterface $stockRepository
    ) {
        $this->walletRepository = $walletRepository;
        $this->stockRepository = $stockRepository;
    }

    public function __invoke(GenerateWeeklyReport $message)
    {
    }
}
