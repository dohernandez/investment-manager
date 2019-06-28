<?php

namespace App\Repository\Criteria;

use App\Entity\StockDividend;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;

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

    public static function projectedAndAnnounced()
    {
        return Criteria::create()
            ->andWhere(
                new CompositeExpression(CompositeExpression::TYPE_OR, [
                    Criteria::expr()->eq('status', StockDividend::STATUS_PROJECTED),
                    Criteria::expr()->eq('status', StockDividend::STATUS_ANNOUNCED),
                ])
            )
            ;
    }
}
