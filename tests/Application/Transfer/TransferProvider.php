<?php

namespace App\Tests\Application\Transfer;

use App\Domain\Transfer\Account;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\Money\Money;
use DateTime;

final class TransferProvider
{
    public static function provide(Account $beneficiary, Account $debtor, Money $amount, DateTime $date): Transfer
    {
        return Transfer::transfer($beneficiary, $debtor, $amount, $date);
    }

    public static function provideAccount(string $id, string $name, string $accountNo): Account
    {
        return new Account($id, $name, $accountNo);
    }
}
