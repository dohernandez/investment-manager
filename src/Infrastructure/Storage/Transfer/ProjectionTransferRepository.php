<?php

namespace App\Infrastructure\Storage\Transfer;

use App\Application\Trasnfer\Repository\ProjectionTransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Transfer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transfer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transfer[]    findAll()
 * @method Transfer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionTransferRepository extends ServiceEntityRepository implements ProjectionTransferRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Transfer::class);
    }
}
