<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Event\AccountUpdated;
use App\Application\Account\Command\DepositMoneyCommand;
use App\Application\Account\Handler\DepositMoneyCommandHandler;
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
final class DepositMoneyHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $id = UUID\Generator::generate();
        $updateAt = new DateTime();
        $balance = Money::fromEURValue(1500);

        $accountAggregate = $this->prophesize(AccountAggregate::class);
        $accountAggregate->getId()->willReturn($id)->shouldBeCalled();
        $accountAggregate->getBalance()->willReturn($balance)->shouldBeCalled();
        $accountAggregate->getUpdatedAt()->willReturn($updateAt)->shouldBeCalled();
        $accountAggregate->deposit($balance)->shouldBeCalled();

        $aggregateRepository = $this->prophesize(EventSourceRepositoryInterface::class);
        $aggregateRepository->load($id, AccountAggregate::class)->willReturn($accountAggregate);
        $aggregateRepository->store(
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

        $handler = new DepositMoneyCommandHandler($aggregateRepository->reveal(), $bus->reveal());
        $handler(new DepositMoneyCommand($id, $balance));
    }
}
