<?php

namespace App\Tests\Domain\Broker;

use App\Domain\Broker\Broker;
use App\Infrastructure\Money\Currency;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

/**
 * @group unit
 * @group domain
 * @group broker
 */
class BrokerTest extends TestCase
{
    public function testRegister()
    {
        $name = 'Broker';
        $site = 'www.broker.com';
        $currency = Currency::eur();

        $broker = Broker::register($name, $site, $currency);

        $this->assertEquals($name, $broker->getName());
        $this->assertEquals($site, $broker->getSite());
        $this->assertEquals($currency, $broker->getCurrency());
    }

    public function testChange()
    {
        $name = 'Broker USD';
        $site = 'www.brokerusd.com';
        $currency = Currency::usd();

        $broker = BrokerProvider::provide('Broker', 'www.broker.com', Currency::eur());

        $broker->change($name, $site, $currency);

        $this->assertEquals($name, $broker->getName());
        $this->assertEquals($site, $broker->getSite());
        $this->assertEquals($currency, $broker->getCurrency());
    }
}
