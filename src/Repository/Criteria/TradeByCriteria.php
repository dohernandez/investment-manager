<?php

namespace App\Repository\Criteria;

use App\Entity\Stock;
use Doctrine\Common\Collections\Criteria;

class TradeByCriteria
{
    public static function byStatus(string $status)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('status', $status))
            ;
        ;
    }

    public static function applyDividend(Stock $stock, \DateTimeInterface $dateAt)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('stock', $stock))
            ->andWhere(Criteria::expr()->gte('openedAt', $dateAt))
            ->andWhere(Criteria::expr()->lte('closedAt', $dateAt))
            ;
        ;
    }
}
