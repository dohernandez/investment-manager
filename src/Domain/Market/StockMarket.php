<?php

namespace App\Domain\Market;

use App\Domain\Market\Event\StockMarketRegistered;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\AggregateRootTypeTrait;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\UUID;
use DateTime;
use Symfony\Component\Intl\Countries;

class StockMarket extends AggregateRoot implements EventSourcedAggregateRoot
{
    use AggregateRootTypeTrait;

    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @var Currency
     */
    private $currency;

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @var string
     */
    private $country;

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCountryName(): string
    {
        return Countries::getName($this->getCountry());
    }

    /**
     * @var string
     */
    private $symbol;

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @var StockMarketMetadata
     */
    private $metadata;

    public function getMetadata(): StockMarketMetadata
    {
        return $this->metadata;
    }

    /**
     * @var string
     */
    private $yahooSymbol;

    public function getYahooSymbol(): string
    {
        return $this->yahooSymbol;
    }

    /**
     * @var DateTime
     */
    private $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @var DateTime
     */
    private $updatedAt;

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getTitle(): string
    {
        return $this->getSymbol() . ' - ' . $this->getName();
    }

    public static function register(
        string $name,
        Currency $currency,
        string $country,
        string $symbol,
        ?string $yahooSymbol = null
    ): self {
        $id = UUID\Generator::generate();

        $self = new static($id);

        $metadata = new StockMarketMetadata();
        $self->recordChange(
            new StockMarketRegistered($id, $name, $currency, $country, $symbol, $metadata, $yahooSymbol)
        );

        return $self;
    }

    protected function apply(Changed $changed)
    {
        switch ($changed->getEventName()) {
            case StockMarketRegistered::class:
                /** @var StockMarketRegistered $event */
                $event = $changed->getPayload();

                $this->id = $changed->getAggregateId();
                $this->name = $event->getName();
                $this->country = $event->getCountry();
                $this->currency = $event->getCurrency();
                $this->symbol = $event->getSymbol();
                $this->metadata = $event->getMetadata();
                $this->metadata = $event->getMetadata();
                $this->yahooSymbol = $event->getYahooSymbol();
                $this->createdAt = $changed->getCreatedAt();
                $this->updatedAt = $changed->getCreatedAt();

                break;
        }
    }
}
