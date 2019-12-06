<?php

namespace App\Domain\Account\Event;

use App\Infrastructure\Money\Currency;

final class AccountOpened
{
    public function __construct(string $id, string $name, string $type, string $accountNo, Currency $currency)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->accountNo = $accountNo;
        $this->currency = $currency;
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
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $type;

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @var string
     */
    private $accountNo;

    public function getAccountNo(): string
    {
        return $this->accountNo;
    }

    /**
     * @var Currency
     */
    private $currency;

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
