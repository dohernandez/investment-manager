<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Event\AccountUpdated;
use App\Application\Account\Command\WithdrawMoneyCommand;
use App\Application\Account\Handler\WithdrawMoneyCommandHandler;
use App\Domain\Account\AccountAggregate;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\Money\Money;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Infrastructure\UUID;

/**
 * @group unit
 * @group application
 * @group account
 */
final class WithdrawMoneyHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $id = UUID\Generator::generate();
        $updateAt = new DateTime();
        $balance = Money::fromEURValue(1500);

        $aggregateAggregate = $this->prophesize(AccountAggregate::class);
        $aggregateAggregate->getId()->willReturn($id)->shouldBeCalled();
        $aggregateAggregate->getBalance()->willReturn($balance)->shouldBeCalled();
        $aggregateAggregate->getUpdatedAt()->willReturn($updateAt)->shouldBeCalled();
        $aggregateAggregate->withdraw($balance)->shouldBeCalled();

        $accountRepository = $this->prophesize(EventSourceRepositoryInterface::class);
        $accountRepository->load($id, AccountAggregate::class)->willReturn($aggregateAggregate);
        $accountRepository->store(
            Argument::that(
                function (AggregateRoot $accountAggregate) use ($id) {
                    $this->assertEquals($id, $accountAggregate->getId());

                    return true;
                }
            )
        )->shouldBeCalled();

        $bus = $this->prophesize(MessageBusInterface::class);
        $bus->dispatch(
            Argument::that(
                function (AccountUpdated $event) use ($id, $updateAt, $balance) {
                    $this->assertEquals($id, $event->getId());
                    $this->assertEquals($updateAt, $event->getUpdatedAt());
                    $this->assertEquals($balance, $event->getBalance());

                    return true;
                }
            )
        )->willReturn(new Envelope(new stdClass()))->shouldBeCalled();

        $handler = new WithdrawMoneyCommandHandler($accountRepository->reveal(), $bus->reveal());
        $handler(new WithdrawMoneyCommand($id, $balance));
    }
}
