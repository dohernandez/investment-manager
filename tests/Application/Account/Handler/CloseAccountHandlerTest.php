<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Command\CloseAccount;
use App\Application\Account\Handler\CloseAccountHandler;
use App\Domain\Account\AccountAggregate;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\AggregateRoot;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;
use Prophecy\Argument;

final class CloseAccountHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $id = UUID\Generator::generate();
        $event = new CloseAccount($id);

        $accountAggregate = $this->prophesize(AccountAggregate::class);
        $accountAggregate->close()->shouldBeCalled();
        $accountAggregate->getId()->willReturn($id)->shouldBeCalled();

        $repo = $this->prophesize(EventSourceRepositoryInterface::class);
        $repo->load($id, AccountAggregate::class)->willReturn($accountAggregate->reveal())->shouldBeCalled();
        $repo->store(
            Argument::that(
                function (AggregateRoot $accountAggregate) use ($id) {
                    $this->assertEquals($id, $accountAggregate->getId());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new CloseAccountHandler($repo->reveal());
        $handler($event);
    }
}
