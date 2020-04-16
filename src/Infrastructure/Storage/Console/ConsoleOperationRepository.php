<?php

namespace App\Infrastructure\Storage\Console;

use App\Domain\Wallet\Operation;
use App\Infrastructure\Money\Money;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

use function serialize;
use function sprintf;

final class ConsoleOperationRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param DateTime $dateAt
     * @param string $type
     * @param int $amount
     * @param Money $value
     * @param Money $commission
     *
     * @return array The operation. [id: ...]
     * @throws \Exception
     */
    public function findByDateAtTypeAmountValueAndCommissions(
        DateTime $dateAt,
        string $type,
        int $amount,
        Money $value,
        Money $commission
    ): ?array {
        if (
        !$operation = $this->em
            ->createQuery(
                sprintf(
                    'SELECT PARTIAL o.{id} 
                                FROM %s o 
                                WHERE DATE(o.dateAt) = DATE(:dateAt) 
                                AND o.type = :type 
                                AND o.amount = :amount 
                                AND o.value = :value 
                                AND o.commission = :commission',
                    Operation::class
                )
            )
            ->setParameter('dateAt', $dateAt)
            ->setParameter('type', $type)
            ->setParameter('amount', $amount)
            ->setParameter('value', serialize($value))
            ->setParameter('commission', serialize($commission))
            ->getOneOrNullResult()

        ) {
            return null;
        }

        $this->em->clear();

        /** @var Operation $operation */
        return [
            'id' => $operation->getId(),
        ];
    }
}
