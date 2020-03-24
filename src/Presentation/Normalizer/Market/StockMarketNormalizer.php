<?php

namespace App\Presentation\Normalizer\Market;

use App\Domain\Market\StockMarket;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Handler allow you to change the serialization, or deserialization process for a single type/format combination.
 *
 * @see http://jmsyst.com/libs/serializer/master/handlers
 */
final class StockMarketNormalizer implements SubscribingHandlerInterface
{
    /**
     * @var ArrayTransformerInterface
     */
    private $serializer;

    public function __construct(ArrayTransformerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => StockMarket::class,
                'method'    => 'serializeTransferToJson',
            ],
        ];
    }

    public function serializeTransferToJson(
        JsonSerializationVisitor $visitor,
        StockMarket $market,
        array $type,
        Context $context
    ) {
        $price = $market->getPrice();

        $displayChange = $price && $price->getChangePrice() ? sprintf(
            '%s (%.2f%%)',
            $price->getChangePrice(),
            $price->getChangePercentage()
        ) : null;

        return [
            'id'            => $market->getId(),
            'name'          => $market->getName(),
            'country'       => $market->getCountry(),
            'countryName'   => $market->getCountryName(),
            'symbol'        => $market->getSymbol(),
            'yahooSymbol'   => $market->getYahooSymbol(),
            'currency'      => $market->getCurrency()->getCurrencyCode(),
            'price'         => $price ? $this->serializer->toArray($price->getPrice()) : null,
            'change'        => $price ? $this->serializer->toArray($price->getChangePrice()) : null,
            'displayChange' => $displayChange,
            'title'         => $market->getTitle(),
        ];
    }
}
