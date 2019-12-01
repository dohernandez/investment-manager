<?php

namespace App\Infrastructure\Storage\Account;

use \App\Application\Account\Storage;
use App\Entity\Account;
use App\Infrastructure\Storage\EntityStorage;
use App\Repository\AccountRepository;

final class Finder implements Storage\Finder
{
    /**
     * @var AccountRepository
     */
    private $repository;

    public function __construct(EntityStorage $storage)
    {
        $this->repository = $storage->getServiceEntityRepository(Account::class);
    }

    public function byId(string $uuid): ?Account
    {
        return $this->repository->find($uuid);
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @inheritDoc
     */
    public function allMatching(string $query, int $limit = 5): array
    {
        return $this->repository->createQueryBuilder('a')
            ->andWhere('a.accountNo LIKE :accountNo OR a.name LIKE :name')
            ->setParameter('accountNo', '%'.$query.'%')
            ->setParameter('name', '%'.$query.'%')
            ->orderBy('a.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
}
