<?php

namespace App\Repository\Criteria;

use Doctrine\Common\Collections\Criteria;

class PositionByCriteria
{
    public static function byStatus(string $status)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('status', $status));
        ;
    }
}
