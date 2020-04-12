<?php

namespace App\Infrastructure\Date;

use DateInterval;
use DateTime;
use DateTimeInterface;

use function sprintf;

final class Date
{
    public const FORMAT_ENGLISH = 'Y-m-d';
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

    public static function now(): DateTime
    {
        $now = new DateTime('now');

        return new DateTime($now->format(self::FORMAT_ENGLISH));
    }

    public static function yearAgo(?int $ago = 1, ?DateTimeInterface $date = null): DateTime
    {
        $now = $date ?? new DateTime('now');

        $interval = new DateInterval(sprintf('P%dY', $ago));
        return new DateTime($now->sub($interval)->format(self::FORMAT_ENGLISH));
    }

    public static function dayAgo(?int $ago = 1, ?DateTimeInterface $date = null): DateTime
    {
        $now = $date ? clone $date : new DateTime('now');

        $interval = new DateInterval(sprintf('P%dD', $ago));
        return new DateTime($now->sub($interval)->format(self::FORMAT_ENGLISH));
    }
}
