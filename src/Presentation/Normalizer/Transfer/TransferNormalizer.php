<?php

namespace App\Presentation\Normalizer\Transfer;

use App\Domain\Transfer\Transfer;
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
final class TransferNormalizer implements SubscribingHandlerInterface
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
                'type'      => Transfer::class,
                'method'    => 'serializeTransferToJson',
            ],
        ];
    }

    public function serializeTransferToJson(JsonSerializationVisitor $visitor, Transfer $transfer, array $type, Context $context)
    {
        return [
            'id' => $transfer->getId(),
            'beneficiary' => $this->serializer->toArray($transfer->getBeneficiaryParty()),
            'debtor' => $this->serializer->toArray($transfer->getDebtorParty()),
            'amount' => $this->serializer->toArray($transfer->getAmount()),
            'date' => $transfer->getDate()->format('c'),
            'title' => $transfer->getTitle(),
        ];
    }
}
