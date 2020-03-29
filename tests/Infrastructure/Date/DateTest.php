<?php

namespace App\Tests\Infrastructure\Date;

use App\Infrastructure\Date\Date;
use DateTime;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    public function testGetYear()
    {
        $dateTime = new DateTime('2019-12-14');
        $this->assertEquals(2019, Date::getYear($dateTime));
    }

    public function testGetMonth()
    {
        $dateTime = new DateTime('2019-12-14');
        $this->assertEquals(12, Date::getMonth($dateTime));
    }

    public function testGetDateTimeBeginYear()
    {
        $this->assertEquals('2020-01-01', Date::getDateTimeBeginYear(2020)->format('Y-m-d'));
    }
}
