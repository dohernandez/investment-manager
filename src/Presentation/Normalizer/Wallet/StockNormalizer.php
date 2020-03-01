<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Domain\Wallet\Stock;
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
final class StockNormalizer implements SubscribingHandlerInterface
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
                'type'      => Stock::class,
                'method'    => 'serializeStockToJson',
            ],
        ];
    }

    public function serializeStockToJson(JsonSerializationVisitor $visitor, Stock $stock, array $type, Context $context)
    {
        return [
            'id' => $stock->getId(),
            'symbol' => $stock->getSymbol(),
            'market' => $this->serializer->toArray($stock->getMarket()),
            'name' => $stock->getName(),
            'price' => $this->serializer->toArray($stock->getPrice()),
            'title' => $stock->getTitle(),
        ];
    }
}
