<?php

namespace App\Repository\Criteria;

use App\Entity\Position;
use App\Entity\Stock;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;

class PositionByCriteria
{
    public static function byStatus(string $status): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('status', $status))
        ;
    }
    public static function ByStockAndOpen(Stock $stock): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('stock', $stock))
            ->andWhere(Criteria::expr()->eq('status', Position::STATUS_OPEN))
        ;
    }

    public function ByStockOpenDateAt(Stock $stock, \DateTimeInterface $datedAt): Criteria
    {
        /*
         * WHERE stock = :stock
         * AND (
         *     openedAt <= :datedAt
         *     AND (
         *         closedAt >= :datedAt
         *         OR closedAt is null
         *     )
         * )
         */
        return Criteria::create()
            ->andWhere(new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    Criteria::expr()->eq('stock', $stock),
                    Criteria::expr()->lte('openedAt', $datedAt),
                    new CompositeExpression(CompositeExpression::TYPE_OR, [
                        Criteria::expr()->gte('closedAt', $datedAt),
                        Criteria::expr()->isNull('closedAt'),
                    ]),
                ]
            ))
            ;
    }
}
