<?php

namespace App\Tests\Application\Broker;

use App\Application\Broker\Command\RegisterBroker;
use App\Application\Broker\RegisterBrokerHandler;
use App\Application\Broker\Repository\BrokerRepositoryInterface;
use App\Domain\Broker\Broker;
use App\Infrastructure\Money\Currency;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class RegisterBrokerHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $name = 'Broker';
        $site = 'www.broker.com';
        $currency = Currency::eur();

        $repo = $this->prophesize(BrokerRepositoryInterface::class);
        $repo->save(
            Argument::that(
                function (Broker $broker) use ($name, $site, $currency) {
                    $this->assertEquals($name, $broker->getName());
                    $this->assertEquals($site, $broker->getSite());
                    $this->assertEquals($currency, $broker->getCurrency());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new RegisterBrokerHandler($repo->reveal());
        $handler(new RegisterBroker($name, $site, $currency));
    }
}
