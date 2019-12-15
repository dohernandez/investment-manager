<?php

namespace App\Tests\Domain\Transfer;

use App\Domain\Transfer\Account;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\Money\Money;
use DateTime;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

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
}
