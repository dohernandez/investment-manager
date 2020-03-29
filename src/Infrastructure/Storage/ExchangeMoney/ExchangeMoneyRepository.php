<?php

namespace App\Infrastructure\Storage\ExchangeMoney;

use App\Application\ExchangeMoney\Repository\ExchangeMoneyRepositoryInterface;
use App\Domain\ExchangeMoney\Rate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Rate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rate[]    findAll()
 * @method Rate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ExchangeMoneyRepository extends ServiceEntityRepository implements ExchangeMoneyRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    public function saveRate(Rate $rate)
    {
        $em = $this->getEntityManager();
        $em->persist($rate);
        $em->flush();
    }

    public function findRateByPaarCurrency(string $paarCurrency): ?Rate
    {
        return $this->findOneBy(
            [
                'paarCurrency' => $paarCurrency,
            ]
        );
    }

    public function findAllByToCurrency(string $toCurrency): array
    {
        return $this->_em->createQueryBuilder('ec')
            ->andWhere('ec.paarCurrency LIKE :toCurrency')
            ->setParameter('toCurrency', '%_' . $toCurrency)
            ->getQuery()
            ->getResult();
    }
}
