<?php

namespace App\Repository\Criteria;

use Doctrine\Common\Collections\Criteria;

class StockDividendByExDateCriteria extends Criteria
{
    public static function createWithExDate(\DateTime $exDate)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->gte('exDate', $exDate))
            ->orderBy(['exDate' => 'DESC']);
            ;
    }

}
