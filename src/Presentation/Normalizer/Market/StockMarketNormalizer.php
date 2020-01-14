<?php

namespace App\Presentation\Normalizer\Market;

use App\Domain\Market\StockMarket;
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
        return [
            'id'          => $market->getId(),
            'name'        => $market->getName(),
            'country'     => $market->getCountry(),
            'countryName' => $market->getCountryName(),
            'symbol'      => $market->getSymbol(),
            'yahooSymbol' => $market->getYahooSymbol(),
            'currency'    => $market->getCurrency()->getCurrencyCode(),
            'title'       => $market->getTitle(),
        ];
    }
}
