<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Command\CloseAccount;
use App\Application\Account\Handler\CloseAccountHandler;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
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

        $account = $this->prophesize(Account::class);
        $account->getId()->willReturn($id)->shouldBeCalled();
        $account->close()->shouldBeCalled();

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);
        $accountRepository->find($id)->willReturn($account->reveal())->shouldBeCalled();
        $accountRepository->save(
            Argument::that(
                function (Account $account) use ($id) {
                    $this->assertEquals($id, $account->getId());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new CloseAccountHandler($accountRepository->reveal());
        $handler($event);
    }
}
