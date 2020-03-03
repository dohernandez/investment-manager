<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
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
        // TODO: Implement findBySlug() method.
    }

    public function findByAccount(string $accountId): ?Wallet
    {
        return $this->findOneBy(
            [
                'accountId' => $accountId
            ]
        );
    }
}
