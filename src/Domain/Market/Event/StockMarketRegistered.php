<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockMarketMetadata;
use App\Infrastructure\Money\Currency;

final class StockMarketRegistered
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
     * @var Currency
     */
    private $currency;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var StockMarketMetadata
     */
    private $metadata;

    public function __construct(string $id, string $name, Currency $currency, string $country, string $symbol, StockMarketMetadata $metadata)
    {
        $this->id = $id;
        $this->name = $name;
        $this->currency = $currency;
        $this->country = $country;
        $this->symbol = $symbol;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
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
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return StockMarketMetadata
     */
    public function getMetadata(): StockMarketMetadata
    {
        return $this->metadata;
    }
}
