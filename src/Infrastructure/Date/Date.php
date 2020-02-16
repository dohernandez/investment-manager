<?php

namespace App\Infrastructure\Date;

use DateTime;

final class Date
{
    public static function getMonth(DateTime $dateTime): int
    {
        return $dateTime->format('n');
    }

    public static function getYear(DateTime $dateTime): int
    {
        return $dateTime->format('Y');
    }
}
