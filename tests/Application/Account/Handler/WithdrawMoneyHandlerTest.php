<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Command\WithdrawMoneyCommand;
use App\Application\Account\Handler\WithdrawMoneyCommandHandler;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Infrastructure\Money\Money;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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

        $account = $this->prophesize(Account::class);
        $account->getId()->willReturn($id)->shouldBeCalled();
        $account->withdraw($balance)->shouldBeCalled();

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);
        $accountRepository->find($id)->willReturn($account);
        $accountRepository->save(
            Argument::that(
                function (Account $account) use ($id) {
                    $this->assertEquals($id, $account->getId());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new WithdrawMoneyCommandHandler($accountRepository->reveal());
        $handler(new WithdrawMoneyCommand($id, $balance));
    }
}
