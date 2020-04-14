<?php

namespace App\Domain\Account\Event;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\EventSource\Event;
use App\Infrastructure\Money\Money;

final class AccountCredited implements DataInterface
{
    use Data;

    public function __construct(string $id, Money $money)
    {
        $this->id = $id;
        $this->money = $money;
    }

    /**
     * @var string
     */
    private $id;

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @var Money
     */
    private $money;

    public function getMoney(): Money
    {
        return $this->money;
    }
}
