<?php

namespace App\Infrastructure\Storage\Market;

use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Domain\Market\StockMarket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StockMarket|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockMarket|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockMarket[]    findAll()
 * @method StockMarket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProjectionStockMarketRepository extends ServiceEntityRepository implements
    ProjectionStockMarketRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockMarket::class);
    }

    public function findBySymbol(string $symbol): ?StockMarket
    {
        return $this->createQueryBuilder('p')
            ->addCriteria(
                Criteria::create()
                    ->andWhere(
                        new CompositeExpression(
                            CompositeExpression::TYPE_OR,
                            [
                                Criteria::expr()->eq('symbol', $symbol),
                                Criteria::expr()->eq('yahooSymbol', $symbol),
                            ]
                        )
                    )
            )
            ->getQuery()
            ->getOneOrNullResult();
    }
}
