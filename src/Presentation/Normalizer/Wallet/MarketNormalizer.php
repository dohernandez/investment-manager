<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Domain\Wallet\Market;
use JMS\Serializer\ArrayTransformerInterface;
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
final class MarketNormalizer implements SubscribingHandlerInterface
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
                'type'      => Market::class,
                'method'    => 'serializeMarketToJson',
            ],
        ];
    }

    public function serializeMarketToJson(JsonSerializationVisitor $visitor, Market $market, array $type, Context $context)
    {
        return [
            'id' => $market->getId(),
            'name' => $market->getName(),
            'symbol' => $market->getSymbol(),
            'title' => $market->getTitle(),
        ];
    }
}
