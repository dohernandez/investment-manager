<?php

namespace App\Infrastructure\Storage\Console;

use App\Domain\Market\Stock;
use Doctrine\ORM\EntityManagerInterface;

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
     * @return string[] List of stock ids listed. [id: ...]
     */
    public function findAllListed(): array
    {
        $allListed = $this->em
            ->createQuery(sprintf('SELECT PARTIAL s.{id} FROM %s s WHERE s.delisted = 0', Stock::class))
            ->getResult();

        return array_map(
            function (Stock $stock) {
                return ['id' => $stock->getId()];
            },
            $allListed
        );
    }
}
