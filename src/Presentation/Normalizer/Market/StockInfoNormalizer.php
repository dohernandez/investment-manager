<?php

namespace App\Presentation\Normalizer\Market;

use App\Domain\Market\StockInfo;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Handler allow you to change the serialization, or deserialization process for a single type/format combination.
 *
 * @see http://jmsyst.com/libs/serializer/master/handlers
 *
 */
final class StockInfoNormalizer implements SubscribingHandlerInterface
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
                'type'      => StockInfo::class,
                'method'    => 'serializeStockInfoToJson',
            ],
        ];
    }

    public function serializeStockInfoToJson(JsonSerializationVisitor $visitor, StockInfo $stockInfo, array $type, Context $context)
    {
        return [
            'id' => $stockInfo->getId(),
            'name' => $stockInfo->getName(),
            'type' => $stockInfo->getType(),
            'title' => $stockInfo->getTitle(),
        ];
    }
}
