<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Command\OpenAccountCommand;
use App\Application\Account\Handler\OpenAccountCommandHandler;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Infrastructure\Money\Currency;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

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

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);

        $accountRepository->save(
            Argument::that(
                function (Account $account) use ($name, $type, $accountNo, $currency) {
                    $this->assertEquals($name, $account->getName());
                    $this->assertEquals($type, $account->getType());
                    $this->assertEquals($accountNo, $account->getAccountNo());
                    $this->assertEquals($currency, $account->getBalance()->getCurrency());
                    $this->assertEquals(0, $account->getBalance()->getValue());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new OpenAccountCommandHandler($accountRepository->reveal());
        $handler(new OpenAccountCommand($name, $type,  $accountNo, $currency));
    }
}
