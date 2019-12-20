<?php

namespace App\Tests\Domain\Broker;

use App\Domain\Broker\Broker;
use App\Domain\Broker\Account;
use App\Infrastructure\Money\Currency;

final class BrokerProvider
{
    public static function provide(Account $account, string $name, string $site, Currency $currency): Transfer
    {
        return Broker::register($account, $name, $site, $currency);
    }
}
