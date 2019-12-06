<?php

namespace App\Infrastructure\Storage;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Projection\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AccountRepository extends ServiceEntityRepository implements AccountRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @inheritDoc
     */
    public function allMatching(string $query, int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.accountNo LIKE :accountNo OR a.name LIKE :name')
            ->setParameter('accountNo', '%'.$query.'%')
            ->setParameter('name', '%'.$query.'%')
            ->orderBy('a.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    public function save(Account $account)
    {
        $this->_em->persist($account);
        $this->_em->flush();
    }
}
