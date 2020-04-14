<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockMarketMetadata;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Currency;

final class StockMarketUpdated implements DataInterface
{
    use Data;

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

    /**
     * @var string|null
     */
    private $yahooSymbol;

    public function __construct(
        string $id,
        string $name,
        Currency $currency,
        string $country,
        string $symbol,
        StockMarketMetadata $metadata,
        ?string $yahooSymbol = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->currency = $currency;
        $this->country = $country;
        $this->symbol = $symbol;
        $this->metadata = $metadata;
        $this->yahooSymbol = $yahooSymbol;
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

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }
}
