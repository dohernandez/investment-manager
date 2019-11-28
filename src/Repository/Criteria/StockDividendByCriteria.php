<?php

namespace App\Repository\Criteria;

use App\Entity\StockDividend;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;

class StockDividendByCriteria extends Criteria
{
    public static function gteExDate(\DateTimeInterface $exDate)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->gte('exDate', $exDate))
            ->orderBy(['exDate' => 'ASC'])
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

    public static function year(int $year)
    {
        $now = new \DateTimeImmutable();
        $beginYear = $now->setDate($year, 1, 1);
        $endYear = $now->setDate($year, 12, 31);

        return Criteria::create()
            ->andWhere(Criteria::expr()->gte('exDate', $beginYear))
            ->andWhere(Criteria::expr()->lt('exDate', $endYear))
            ;
    }

    public static function lastPaidAtDate(?\DateTimeInterface $datetime)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->lt('exDate', $datetime))
            ->andWhere(Criteria::expr()->eq('status', StockDividend::STATUS_PAYED))
            ->orderBy(['exDate' => 'DESC'])
            ->setMaxResults(1)
            ;
    }

    public static function toPayDividend(\DateTimeInterface $date)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->lte('exDate', $date))
            ->andWhere(Criteria::expr()->gte('paymentDate', $date))
            ->orderBy(['paymentDate' => 'ASC'])
            ;
    }
}
