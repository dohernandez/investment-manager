<?php

namespace App\Domain\Market;

use App\Domain\Market\Event\StockMarketPriceUpdated;
use App\Domain\Market\Event\StockMarketRegistered;
use App\Domain\Market\Event\StockMarketUpdated;
use App\Infrastructure\Doctrine\DataReference;
use App\Infrastructure\Doctrine\DBAL\DataReferenceInterface;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\AggregateRootTypeTrait;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\UUID;
use DateTime;
use Symfony\Component\Intl\Countries;

class StockMarket extends AggregateRoot implements EventSourcedAggregateRoot, DataReferenceInterface
{
    use AggregateRootTypeTrait;
    use DataReference;

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

    /**
     * @var MarketPrice|null
     */
    private $price;

    public function getPrice(): ?MarketPrice
    {
        return $this->price;
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

    public function update(
        string $name,
        Currency $currency,
        string $country,
        string $symbol,
        ?string $yahooSymbol = null
    ): self {
        $this->recordChange(
            new StockMarketUpdated(
                $this->id,
                $name,
                $currency,
                $country,
                $symbol,
                $this->metadata,
                $yahooSymbol
            )
        );

        return $this;
    }

    public function updatePrice(MarketPrice $price, $toUpdateAt = 'now'): self
    {
        $toUpdateAt = new DateTime($toUpdateAt);

        if ($this->price) {
            $price = $this->price->update($price);
        } else {
            $price->setMarket($this);
        }

        if ($changed = $this->findIfLastChangeHappenedIsName(StockMarketPriceUpdated::class)) {
            // This is to avoid have too much update events.
            $this->replaceChangedPayload(
                $changed,
                new StockMarketPriceUpdated(
                    $this->getId(),
                    $price,
                    $toUpdateAt
                ),
                clone $toUpdateAt
            );

            return $this;
        }

        $this->recordChange(
            new StockMarketPriceUpdated(
                $this->getId(),
                $price,
                $toUpdateAt
            )
        );

        return $this;
    }

    protected function apply(Changed $changed)
    {
        $this->updatedAt = $changed->getCreatedAt();

        $event = $changed->getPayload();

        switch ($changed->getEventName()) {
            case StockMarketRegistered::class:
                /** @var StockMarketRegistered $event */

                $this->id = $changed->getAggregateId();
                $this->name = $event->getName();
                $this->country = $event->getCountry();
                $this->currency = $event->getCurrency();
                $this->symbol = $event->getSymbol();
                $this->metadata = $event->getMetadata();
                $this->yahooSymbol = $event->getYahooSymbol();
                $this->createdAt = $changed->getCreatedAt();

                break;

            case StockMarketUpdated::class:
                /** @var StockMarketUpdated $event */

                $this->name = $event->getName();
                $this->country = $event->getCountry();
                $this->currency = $event->getCurrency();
                $this->symbol = $event->getSymbol();
                $this->metadata = $event->getMetadata();
                $this->yahooSymbol = $event->getYahooSymbol();

                break;

            case StockMarketPriceUpdated::class:
                /** @var StockMarketPriceUpdated $event */
                $event = $changed->getPayload();

                $this->price = $event->getPrice();

                $this->updatedAt = $changed->getCreatedAt();
                break;
        }
    }
}
