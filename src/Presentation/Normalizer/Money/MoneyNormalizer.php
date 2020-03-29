<?php

namespace App\Presentation\Normalizer\Money;

use App\Infrastructure\Money\Money;
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
class MoneyNormalizer implements SubscribingHandlerInterface
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
                'type'      => Money::class,
                'method'    => 'serializeMoneyToJson',
            ],
        ];
    }

    public function serializeMoneyToJson(JsonSerializationVisitor $visitor, Money $money, array $type, Context $context)
    {
        return [
            'currency' => $this->serializer->toArray($money->getCurrency()),
            'value' => $money->getValue(),
            'precision' => $money->getPrecision(),
            'preciseValue' => $money->getPreciseValue(),
            'displayValue' => (string) $money,
        ];
    }
}
