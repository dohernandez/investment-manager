<?php

namespace App\Tests\Domain\Broker;

use App\Domain\Broker\Broker;
use App\Domain\Broker\Account;
use App\Infrastructure\Money\Currency;

final class BrokerProvider
{
    public static function provide(string $name, string $site, Currency $currency): Broker
    {
        return Broker::register($name, $site, $currency);
    }
}
