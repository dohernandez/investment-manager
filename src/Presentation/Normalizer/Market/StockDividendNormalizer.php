<?php

namespace App\Presentation\Normalizer\Market;

use App\Domain\Market\StockDividend;
use App\Domain\Market\StockInfo;
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
final class StockDividendNormalizer implements SubscribingHandlerInterface
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
                'type'      => StockDividend::class,
                'method'    => 'serializeStockDividendInfoToJson',
            ],
        ];
    }

    public function serializeStockDividendInfoToJson(JsonSerializationVisitor $visitor, StockDividend $stockDividend, array $type, Context $context)
    {
        return [
            'id' => $stockDividend->getId(),
            'exDate' => $stockDividend->getExDate()->format('c'),
            'recordDate' => $stockDividend->getRecordDate() ? $stockDividend->getRecordDate()->format('c') : null,
            'paymentDate' => $stockDividend->getPaymentDate() ? $stockDividend->getPaymentDate()->format('c') : null,
            'status' => $stockDividend->getStatus(),
            'value' => $stockDividend->getValue() ? $this->serializer->toArray($stockDividend->getValue()) : null,
            'title' => $stockDividend->getTitle(),
        ];
    }
}
