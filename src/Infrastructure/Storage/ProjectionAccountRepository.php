<?php

namespace App\Infrastructure\Storage;

use App\Application\Account\Repository\ProjectionAccountRepositoryInterface;
use App\Domain\Account\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionAccountRepository extends ServiceEntityRepository implements ProjectionAccountRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @inheritDoc
     */
    public function findAllOpenMatching(string $query, int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.accountNo LIKE :accountNo OR a.name LIKE :name')
            ->setParameter('accountNo', '%'.$query.'%')
            ->setParameter('name', '%'.$query.'%')
            ->andWhere('a.isClosed = false')
            ->orderBy('a.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @inheritDoc
     */
    public function findAllOpen(): array
    {
        return $this->findBy(['isClosed' => false]);
    }

    /**
     * @inheritDoc
     */
    public function findByAccountNo(string $accountNo): ?Account
    {
        return $this->findOneBy(['accountNo' => $accountNo]);
    }
}
