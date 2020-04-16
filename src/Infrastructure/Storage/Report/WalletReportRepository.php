<?php

namespace App\Infrastructure\Storage\Report;

use App\Application\Report\Repository\WalletReportRepositoryInterface;
use App\Domain\Report\WalletReport;
use App\Domain\Report\Wallet\Wallet;
use App\Infrastructure\Date\Date;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class WalletReportRepository extends ServiceEntityRepository implements WalletReportRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WalletReport::class);
    }

    public function findByWalletTypeAndDateAt(Wallet $wallet, string $type, DateTime $dateAt): ?WalletReport
    {
        return $this->findOneBy(
            [
                'walletId' => $wallet->getId(),
                'type'     => $type,
                'name'      => $dateAt->format(Date::FORMAT_SPANISH),
            ]
        );
    }

    public function save(WalletReport $report): void
    {
        $this->_em->persist($report);
        $this->_em->flush();
    }
}
