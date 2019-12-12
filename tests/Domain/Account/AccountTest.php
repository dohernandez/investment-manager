<?php

namespace App\Tests\Domain\Account;

use App\Domain\Account\Account;
use App\Domain\Account\Event\AccountCredited;
use App\Domain\Account\Event\AccountOpened;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\Metadata;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

/**
 * @group unit
 * @group domain
 * @group account
 */
class AccountTest extends TestCase
{
    public function testOpen()
    {
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $currency = Currency::eur();

        $accountAggregate = Account::open($name, $type, $accountNo, $currency);

        $this->assertEquals($name, $accountAggregate->getName());
        $this->assertEquals($type, $accountAggregate->getType());
        $this->assertEquals($accountNo, $accountAggregate->getAccountNo());
        $this->assertEquals($currency, $accountAggregate->getBalance()->getCurrency());
    }

    public function testDeposit()
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $currency = Currency::eur();

        $accountOpened = new AccountOpened($id, $name, $type, $accountNo, $currency);

        $account = new Account($id);
        $account->replay(
                [
                    new Changed(
                        UUID\Generator::generate(),
                        get_class($accountOpened),
                        $accountOpened,
                        new Metadata(),
                        get_class($account),
                        $id,
                        1
                    )
                ]
            )
        ;

        $deposit = Money::fromEURValue(1500);
        $account->deposit($deposit);

        $this->assertEquals($id, $account->getId());
        $this->assertEquals($name, $account->getName());
        $this->assertEquals($type, $account->getType());
        $this->assertEquals($accountNo, $account->getAccountNo());
        $this->assertEquals($deposit, $account->getBalance());
    }

    public function testWithdraw()
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $currency = Currency::eur();

        $accountOpened = new AccountOpened($id, $name, $type, $accountNo, $currency);

        $deposit = Money::fromEURValue(1500);
        $accountCredited = new AccountCredited($id, $deposit);


        $account = new Account($id);
        $account->replay(
                [
                    new Changed(
                        UUID\Generator::generate(),
                        get_class($accountOpened),
                        $accountOpened,
                        new Metadata(),
                        get_class($account),
                        $id,
                        1
                    ),
                    new Changed(
                        UUID\Generator::generate(),
                        get_class($accountCredited),
                        $accountCredited,
                        new Metadata(),
                        get_class($account),
                        $id,
                        1
                    )
                ]
            )
        ;

        $credited = Money::fromEURValue(500);
        $account->withdraw($credited);

        $this->assertEquals($id, $account->getId());
        $this->assertEquals($name, $account->getName());
        $this->assertEquals($type, $account->getType());
        $this->assertEquals($accountNo, $account->getAccountNo());
        $this->assertEquals(1000, $account->getBalance()->getValue());
    }

    public function testClose()
    {
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $currency = Currency::eur();

        $account = Account::open($name, $type, $accountNo, $currency);
        $account->close();

        $this->assertTrue($account->isClosed());
    }
}
