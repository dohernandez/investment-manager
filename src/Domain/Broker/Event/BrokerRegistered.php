<?php

namespace App\Domain\Broker\Event;

use App\Infrastructure\Money\Currency;

class BrokerRegistered
{
    /**
     * @var string
     */
    private $id;

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

    public function __construct(string $id, string $name, string $site, Currency $currency)
    {
        $this->id = $id;
        $this->name = $name;
        $this->site = $site;
        $this->currency = $currency;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
