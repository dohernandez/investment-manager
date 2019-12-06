<?php

namespace App\Tests\Application\Account\Handler;

use App\Application\Account\Event\AccountCreated;
use App\Application\Account\Handler\CreateAccountProjectionHandler;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Projection\Account;
use App\Infrastructure\Money\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use App\Infrastructure\UUID;

/**
 * @group unit
 * @group application
 * @group account
 */
final class CreateAccountProjectionHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $balance = Money::fromEURValue(0);
        $createdAt = new DateTimeImmutable();

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);
        $accountRepository->save(
            Argument::that(
                function (Account $account) use ($id, $name, $type, $accountNo, $balance, $createdAt) {
                    $this->assertEquals($id, $account->getId());
                    $this->assertEquals($name, $account->getName());
                    $this->assertEquals($type, $account->getType());
                    $this->assertEquals($accountNo, $account->getAccountNo());
                    $this->assertEquals($balance, $account->getBalance());
                    $this->assertEquals($createdAt, $account->getCreatedAt());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new CreateAccountProjectionHandler($accountRepository->reveal());
        $handler(new AccountCreated($id, $name, $type, $accountNo, $balance, $createdAt));
    }
}
