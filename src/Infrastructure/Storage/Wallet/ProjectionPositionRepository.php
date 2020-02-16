<?php

namespace App\Infrastructure\Storage\Wallet;

use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Domain\Wallet\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Position|null find($id, $lockMode = null, $lockVersion = null)
 * @method Position|null findOneBy(array $criteria, array $orderBy = null)
 * @method Position[]    findAll()
 * @method Position[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionPositionRepository extends ServiceEntityRepository implements ProjectionPositionRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function findByStock(string $stockId): ?Position
    {
        return $this->findOneBy(
            [
                'stockId' => $stockId
            ]
        );
    }
}
