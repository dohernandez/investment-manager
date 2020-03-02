<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Domain\Wallet\Position;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Position|null find($id, $lockMode = null, $lockVersion = null)
 * @method Position|null findOneBy(array $criteria, array $orderBy = null)
 * @method Position[]    findAll()
 * @method Position[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionPositionRepository extends ServiceEntityRepository implements
    ProjectionPositionRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function findByWalletStock(string $walletId, string $stockId, ?string $status = null): ?Position
    {
        $whereStatus = [];
        if ($status !== null || $status !== '') {
            $whereStatus = [
                'status' => $status,
            ];
        }

        return $this->findOneBy(
            [
                'wallet'  => $walletId,
                'stockId' => $stockId,
            ] + $whereStatus
        );
    }

    /**
     * @inheritDoc
     */
    public function findAllByWallet(string $walletId): array
    {
        return $this->findBy(['wallet' => $walletId]);
    }

    /**
     * @inheritDoc
     */
    public function findAllByWalletStatus(string $walletId, string $status): array
    {
        return $this->findBy(
            [
                'wallet' => $walletId,
                'status' => $status,
            ]
        );
    }

    public function findByWalletStockOpenDateAt(string $walletId, string $stockId, DateTime $datedAt): ?Position
    {
        /*
         * WHERE wallet = :walletId
         * AND stock = :stockId
         * AND (
         *     openedAt <= :datedAt
         *     AND (
         *         closedAt >= :datedAt
         *         OR closedAt is null
         *     )
         * )
         */
        return $this->createQueryBuilder('p')
            ->andWhere('p.wallet = :walletId AND p.stockId = :stockId')
            ->setParameter('walletId', $walletId)
            ->setParameter('stockId', $stockId)
            ->andWhere('p.openedAt <= :datedAt AND (p.closedAt >= :datedAt OR p.closedAt is null)')
            ->setParameter('datedAt', $datedAt)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
