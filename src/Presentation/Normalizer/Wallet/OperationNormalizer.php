<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Domain\Wallet\Operation;
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
final class OperationNormalizer implements SubscribingHandlerInterface
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
                'type'      => Operation::class,
                'method'    => 'serializeOperationToJson',
            ],
        ];
    }

    public function serializeOperationToJson(JsonSerializationVisitor $visitor, Operation $operation, array $type, Context $context)
    {
        return [
            'id' => $operation->getId(),
            'dateAt' => $operation->getDateAt()->format('c'),
            'type' => $operation->getType(),
            'stock' => $operation->getStock() ? $this->serializer->toArray($operation->getStock()) : null,
            'price' => $operation->getPrice() ? $this->serializer->toArray($operation->getPrice()) : null,
            'amount' => $operation->getAmount(),
            'value' => $operation->getValue() ? $this->serializer->toArray($operation->getValue()) : null,
            'commissions' => $operation->getCommissionsPaid() ?
                $this->serializer->toArray($operation->getCommissionsPaid()) :
                null,

            'title' => $operation->getTitle(),
        ];
    }
}
