<?php

namespace App\Tests\Domain\Transfer;

use App\Domain\Transfer\Account;
use App\Domain\Transfer\Exception\TransferRemovedException;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use App\Tests\Application\Transfer\TransferProvider;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group domain
 * @group transfer
 */
final class TransferTest extends TestCase
{
    public function testTransfer()
    {
        $beneficiary = new Account(UUID\Generator::generate(), 'Random Iban 1', 'DE67500105176511458445');
        $debtor = new Account(UUID\Generator::generate(), 'Random Iban 2', 'NL50ABNA9904789940');
        $amount = Money::fromEURValue(1000);
        $date = new DateTime();

        $transfer = Transfer::transfer($beneficiary, $debtor, $amount, $date);

        $this->assertEquals($beneficiary, $transfer->getBeneficiaryParty());
        $this->assertEquals($debtor, $transfer->getDebtorParty());
        $this->assertEquals($amount, $transfer->getAmount());
        $this->assertEquals($date, $transfer->getDate());
    }

    public function testChange()
    {
        $transfer = TransferProvider::provide(
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Beneficiary', 'DE83726'),
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Debtor', 'DE83726'),
            Money::fromEURValue(1000),
            new DateTime('now', new DateTimeZone('UTC'))
        );

        $beneficiary = new Account(UUID\Generator::generate(), 'Random Iban 1', 'DE67500105176511458445');
        $debtor = new Account(UUID\Generator::generate(), 'Random Iban 2', 'NL50ABNA9904789940');
        $amount = Money::fromEURValue(1000);
        $date = new DateTime();

        $transfer->change($beneficiary, $debtor, $amount, $date);

        $this->assertEquals($beneficiary, $transfer->getBeneficiaryParty());
        $this->assertEquals($debtor, $transfer->getDebtorParty());
        $this->assertEquals($amount, $transfer->getAmount());
        $this->assertEquals($date, $transfer->getDate());
    }

    public function testRemoved()
    {
        $transfer = TransferProvider::provide(
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Beneficiary', 'DE83726'),
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Debtor', 'DE83726'),
            Money::fromEURValue(1000),
            new DateTime('now', new DateTimeZone('UTC'))
        );

        $transfer->remove();

        // Try to change after being removed, but not possible
        $amount = Money::fromEURValue(1000);

        $this->expectException(TransferRemovedException::class);
        $this->expectExceptionMessage('Change not possible, transfer removed.');

        $transfer->change(
            $transfer->getBeneficiaryParty(),
            $transfer->getDebtorParty(),
            $amount,
            $transfer->getDate()
        );

        // Try to remove again after being removed, but not possible
        $this->expectException(TransferRemovedException::class);
        $this->expectExceptionMessage('Remove not possible, transfer removed.');

        $transfer->remove();
    }
}
