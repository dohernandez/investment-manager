<?php

namespace App\Tests\Infrastructure\Storage;

use App\Domain\Account\Account;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;

final class AccountProvider
{
    public static function provide($name, $type, $accountNo): Account
    {
        return Account::open($name, $type, $accountNo, Currency::eur());
    }

    public static function provideWithDeposit($name, $type, $accountNo, $deposit): Account
    {
        $deposit = Money::fromEURValue($deposit);

        $account = self::provide($name, $type, $accountNo);

        $account->deposit($deposit);

        return $account;
    }
}
