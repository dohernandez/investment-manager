<?php

namespace App\Infrastructure\Storage\Console;

use App\Domain\Market\Stock;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Query\ResultSetMapping;

use Doctrine\ORM\Query\ResultSetMappingBuilder;

use function array_map;
use function sprintf;

final class ConsoleStockRepository
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
     * @return array List of stock listed. [id: ...]
     */
    public function findAllListed(): array
    {
        $allListed = $this->em
            ->createQuery(sprintf('SELECT PARTIAL s.{id} FROM %s s WHERE s.delisted = 0', Stock::class))
            ->getResult();

        return array_map(
            function (Stock $stock) {
                return [
                    'id' => $stock->getId(),
                ];
            },
            $allListed
        );
    }

    /**
     * @param string $symbol
     *
     * @return array The stock. [id: ...]
     */
    public function findBySymbol(string $symbol): array
    {
        if (
            !$stock = $this->em
                ->createQuery(sprintf('SELECT PARTIAL s.{id} FROM %s s WHERE s.symbol = :symbol', Stock::class))
                ->setParameter('symbol', $symbol)
                ->getOneOrNullResult()
        ) {
            return null;
        }

        /** @var Stock $stock */
        return [
            'id' => $stock->getId(),
        ];
    }
}