<?php

namespace App\Presentation\Normalizer\Money;

use App\Infrastructure\Money\Currency;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

class CurrencyNormalizer implements SubscribingHandlerInterface
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
                'type'      => Currency::class,
                'method'    => 'serializeCurrencyToJson',
            ],
        ];
    }

    public function serializeCurrencyToJson(JsonSerializationVisitor $visitor, Currency $currency, array $type, Context $context)
    {
        return [
            'symbol' => $currency->getSymbol(),
            'currencyCode' => $currency->getCurrencyCode(),
        ];
    }
}
