<?php

namespace App\Application\Report\Repository;

use App\Domain\Report\Wallet\Wallet;
use App\Domain\Report\WalletReport;
use DateTime;

interface WalletReportRepositoryInterface
{
    public function findByWalletTypeAndDateAt(Wallet $wallet, string $type, DateTime $dateAt): ?WalletReport;

    public function save(WalletReport $report): void;
}
