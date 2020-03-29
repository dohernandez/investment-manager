<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Command\DepositMoneyCommand;
use App\Application\Account\Handler\DepositMoneyCommandHandler;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Infrastructure\Money\Money;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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
        $balance = Money::fromEURValue(1500);

        $account = $this->prophesize(Account::class);
        $account->getId()->willReturn($id)->shouldBeCalled();

        $account->deposit($balance)->shouldBeCalled();

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

        $handler = new DepositMoneyCommandHandler($accountRepository->reveal());
        $handler(new DepositMoneyCommand($id, $balance));
    }
}
