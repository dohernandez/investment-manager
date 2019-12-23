<?php

namespace App\Application\Broker\Command;

use App\Infrastructure\Money\Currency;

final class RegisterBroker
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $site;

    /**
     * @var Currency
     */
    private $currency;

    public function __construct(string $name, string $site, Currency $currency)
    {
        $this->name = $name;
        $this->site = $site;
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSite(): string
    {
        return $this->site;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
