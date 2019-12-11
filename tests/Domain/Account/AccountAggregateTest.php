<?php

namespace App\Tests\Domain\Account;

use App\Domain\Account\AccountAggregate;
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
class AccountAggregateTest extends TestCase
{
    public function testOpen()
    {
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $currency = Currency::eur();

        $accountAggregate = AccountAggregate::open($name, $type, $accountNo, $currency);

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

        $accountAggregate = new AccountAggregate($id);
        $accountAggregate->replay(
                [
                    new Changed(
                        UUID\Generator::generate(),
                        get_class($accountOpened),
                        $accountOpened,
                        new Metadata(),
                        get_class($accountAggregate),
                        $id,
                        1
                    )
                ]
            )
        ;

        $deposit = Money::fromEURValue(1500);
        $accountAggregate->deposit($deposit);

        $this->assertEquals($id, $accountAggregate->getId());
        $this->assertEquals($name, $accountAggregate->getName());
        $this->assertEquals($type, $accountAggregate->getType());
        $this->assertEquals($accountNo, $accountAggregate->getAccountNo());
        $this->assertEquals($deposit, $accountAggregate->getBalance());
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


        $accountAggregate = new AccountAggregate($id);
        $accountAggregate->replay(
                [
                    new Changed(
                        UUID\Generator::generate(),
                        get_class($accountOpened),
                        $accountOpened,
                        new Metadata(),
                        get_class($accountAggregate),
                        $id,
                        1
                    ),
                    new Changed(
                        UUID\Generator::generate(),
                        get_class($accountCredited),
                        $accountCredited,
                        new Metadata(),
                        get_class($accountAggregate),
                        $id,
                        1
                    )
                ]
            )
        ;

        $credited = Money::fromEURValue(500);
        $accountAggregate->withdraw($credited);

        $this->assertEquals($id, $accountAggregate->getId());
        $this->assertEquals($name, $accountAggregate->getName());
        $this->assertEquals($type, $accountAggregate->getType());
        $this->assertEquals($accountNo, $accountAggregate->getAccountNo());
        $this->assertEquals(1000, $accountAggregate->getBalance()->getValue());
    }
}
