<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ProjectionOperationRepositoryInterface;
use App\Domain\Wallet\Operation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionOperationRepository extends ServiceEntityRepository implements ProjectionOperationRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Operation::class);
    }
}
