<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Event\AccountUpdated;
use App\Application\Account\Handler\UpdateAccountProjectionHandler;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Projection\Account;
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
final class UpdateAccountProjectionHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $balance = Money::fromEURValue(0);
        $updatedAt = new DateTime();

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);
        $accountRepository->find($id)->willReturn(new Account($id))->shouldBeCalled();
        $accountRepository->save(
            Argument::that(
                function (Account $account) use ($id, $balance, $updatedAt) {
                    $this->assertEquals($id, $account->getId());
                    $this->assertEquals($balance, $account->getBalance());
                    $this->assertEquals($updatedAt, $account->getUpdatedAt());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new UpdateAccountProjectionHandler($accountRepository->reveal());
        $handler(new AccountUpdated($id, $balance, $updatedAt));
    }
}
