<?php

namespace App\Infrastructure\Date;

use DateTime;
use DateTimeInterface;

final class Date
{
    public const FORMAT_SPANISH = 'd/m/Y';

    public static function getMonth(DateTimeInterface $dateTime): int
    {
        return $dateTime->format('n');
    }

    public static function getYear(DateTimeInterface $dateTime): int
    {
        return $dateTime->format('Y');
    }

    public static function getDateTimeBeginYear(?int $year = null): DateTime
    {
        if ($year === null) {
            $year = Date::getYear(new DateTime());
        }

        return new DateTime($year . '-01-01');
    }
}
