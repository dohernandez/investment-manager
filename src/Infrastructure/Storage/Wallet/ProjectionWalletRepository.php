<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Domain\Wallet\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Wallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallet[]    findAll()
 * @method Wallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionWalletRepository extends ServiceEntityRepository implements ProjectionWalletRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function findBySlug(string $slug): ?Wallet
    {
        return $this->findOneBy(
            [
                'slug' => $slug,
            ]
        );
    }

    public function findByAccount(string $accountId): ?Wallet
    {
        return $this->findOneBy(
            [
                'accountId' => $accountId,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function findAllByStockOnOpenPosition(string $stockId): array
    {
        return $this->createQueryBuilder('w')
            ->distinct()
            ->innerJoin('w.positions', 'p')
            ->andWhere('p.stockId = :stockId')
            ->andWhere('p.status = :status')
            ->setParameter('stockId', $stockId)
            ->setParameter('status', Position::STATUS_OPEN)
            ->getQuery()
            ->getResult()
            ;
    }
}
