<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Domain\Wallet\Broker;
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
final class BrokerNormalizer implements SubscribingHandlerInterface
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
                'type'      => Broker::class,
                'method'    => 'serializeBrokerToJson',
            ],
        ];
    }

    public function serializeBrokerToJson(JsonSerializationVisitor $visitor, Broker $broker, array $type, Context $context)
    {
        return [
            'id' => $broker->getId(),
            'name' => $broker->getName(),
            'currency' => $broker->getCurrency(),
            'title' => $broker->getTitle(),
        ];
    }
}
