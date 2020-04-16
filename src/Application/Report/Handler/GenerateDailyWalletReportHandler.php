<?php

namespace App\Application\Report\Handler;

use App\Application\Report\Command\GenerateDailyWalletReport;
use App\Application\Report\Repository\PositionRepositoryInterface;
use App\Application\Report\Repository\WalletReportRepositoryInterface;
use App\Application\Report\Repository\WalletRepositoryInterface;
use App\Domain\Report\Wallet\Content;
use App\Domain\Report\WalletReport;
use App\Infrastructure\Date\Date;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class GenerateDailyWalletReportHandler implements MessageHandlerInterface
{
    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    /**
     * @var PositionRepositoryInterface
     */
    private $positionRepository;

    /**
     * @var WalletReportRepositoryInterface
     */
    private $walletReportRepository;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        PositionRepositoryInterface $positionRepository,
        WalletReportRepositoryInterface $walletReportRepository
    ) {
        $this->walletRepository = $walletRepository;
        $this->positionRepository = $positionRepository;
        $this->walletReportRepository = $walletReportRepository;
    }

    public function __invoke(GenerateDailyWalletReport $message)
    {
        $wallet = $this->walletRepository->find($message->getWalletId());
        $positions = $this->positionRepository->findAllOpenByWallet($message->getWalletId());

        $report = $this->walletReportRepository->findByWalletTypeAndDateAt(
            $wallet,
            WalletReport::WALLET_REPORT_DAILY_TYPE,
            $message->getDateAt()
        );

        if (!$report) {
            $report = (new WalletReport())
                ->setType(WalletReport::WALLET_REPORT_DAILY_TYPE)
                ->setName($message->getDateAt()->format(Date::FORMAT_SPANISH));
        }

        $report->setContent(new Content($wallet, $positions));

        $this->walletReportRepository->save($report);
    }
}
