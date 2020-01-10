<?php

namespace App\Application\Broker\Command;

use App\Infrastructure\Money\Currency;

final class ChangeBroker
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
