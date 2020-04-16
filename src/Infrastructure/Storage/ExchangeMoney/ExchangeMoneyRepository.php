<?php

namespace App\Infrastructure\Storage\ExchangeMoney;

use App\Application\ExchangeMoney\Repository\ExchangeMoneyRepositoryInterface;
use App\Domain\ExchangeMoney\Rate;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

use function sprintf;

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

    /**
     * @inheritDoc
     */
    public function findAllByToCurrency(string $toCurrency): array
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.paarCurrency LIKE :toCurrency')
            ->setParameter('toCurrency', $toCurrency . '_%')
            ->getQuery()
            ->getResult();
    }

    public function findRateByPaarCurrencyDateAt(string $paarCurrency, DateTime $date): ?Rate
    {
        return $this->findOneBy(
            [
                'paarCurrency' => $paarCurrency,
                'dateAt'       => $date,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function findAllLatest()
    {
        $em = $this->getEntityManager();

        $classMetadata = $this->getEntityManager()->getClassMetadata(Rate::class);

        $rsm = $this->createResultSetMapping($classMetadata);

        return $em->createNativeQuery(
            sprintf(
                'SELECT er0.*
                 FROM %1$s er0
                 WHERE (er0.paar_currency, er0.date_at) IN (
                   SELECT DISTINCT er1.paar_currency, MAX(er1.date_at)
                   FROM %1$s er1
                   GROUP BY 1
                )',
                $classMetadata->getTableName()
            ),
            $rsm
        )
            ->getResult();
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return ResultSetMapping
     */
    private function createResultSetMapping(ClassMetadata $classMetadata): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $alias = 'er0';
        $rsm->addEntityResult(Rate::class, '' . $alias . '');

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $rsm->addFieldResult($alias, $classMetadata->getColumnName($fieldName), $fieldName);
        }

        return $rsm;
    }

}
