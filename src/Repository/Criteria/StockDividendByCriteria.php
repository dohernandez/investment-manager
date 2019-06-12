<?php

namespace App\Repository\Criteria;

use Doctrine\Common\Collections\Criteria;

class StockDividendByCriteria extends Criteria
{
    public static function nextExDate(\DateTime $exDate)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->gte('exDate', $exDate))
            ->orderBy(['exDate' => 'ASC']);
            ;
    }

    public static function lastExDate(\DateTime $exDate)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->lt('exDate', $exDate))
            ->orderBy(['exDate' => 'DESC']);
            ;
    }
}
