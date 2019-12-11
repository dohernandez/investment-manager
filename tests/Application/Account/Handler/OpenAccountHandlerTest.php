<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Event\AccountCreated;
use App\Application\Account\Command\OpenAccountCommand;
use App\Application\Account\Handler\OpenAccountCommandHandler;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\Money\Currency;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @group unit
 * @group application
 * @group account
 */
final class OpenAccountHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $currency = Currency::eur();

        $aggregateRepository = $this->prophesize(EventSourceRepositoryInterface::class);

        $aggregateRepository->store(
            Argument::that(
                function (AggregateRoot $accountAggregate) use ($name, $type, $accountNo, $currency) {
                    $this->assertEquals($name, $accountAggregate->getName());
                    $this->assertEquals($type, $accountAggregate->getType());
                    $this->assertEquals($accountNo, $accountAggregate->getAccountNo());
                    $this->assertEquals($currency, $accountAggregate->getBalance()->getCurrency());
                    $this->assertEquals(0, $accountAggregate->getBalance()->getValue());

                    return true;
                }
            )
        )->shouldBeCalled();

        $bus = $this->prophesize(MessageBusInterface::class);
        $bus->dispatch(Argument::that(function (AccountCreated $event) use ($name, $type, $accountNo, $currency) {
            $this->assertNotEmpty($event->getId());
            $this->assertEquals($name, $event->getName());
            $this->assertEquals($type, $event->getType());
            $this->assertEquals($accountNo, $event->getAccountNo());
            $this->assertEquals($currency, $event->getBalance()->getCurrency());
            $this->assertEquals(0, $event->getBalance()->getValue());
            $this->assertNotEmpty($event->getCreatedAt());

            return true;
        }))->willReturn(new Envelope(new stdClass()))->shouldBeCalled();

        $handler = new OpenAccountCommandHandler($aggregateRepository->reveal(), $bus->reveal());
        $handler(new OpenAccountCommand($name, $type,  $accountNo, $currency));
    }
}
