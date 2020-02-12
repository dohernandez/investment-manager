<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Domain\Wallet\WalletBook;
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
final class WalletMetadataNormalizer implements SubscribingHandlerInterface
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
                'type'      => WalletBook::class,
                'method'    => 'serializeWalletMetadataToJson',
            ],
        ];
    }

    public function serializeWalletMetadataToJson(
        JsonSerializationVisitor $visitor,
        WalletBook $metadata,
        array $type,
        Context $context
    ) {
        $pBenefits = null;
        if ($metadata->getInvested() && $metadata->getBenefits()) {
            $pBenefits = $metadata->getInvested()->getValue() ?
                $metadata->getBenefits()->getValue() * 100 / $metadata->getInvested()->getValue() :
                0;
        }

        return [
            'invested' => $this->serializer->toArray($metadata->getInvested()),
            'capital' => $this->serializer->toArray($metadata->getCapital()),
            'funds' => $this->serializer->toArray($metadata->getFunds()),
            'dividend' => $metadata->getDividend(),
            'commissions' => $metadata->getCommissions(),
            'connection' => $metadata->getConnection(),
            'interest' => $metadata->getInterest(),
            'benefits' => $this->serializer->toArray($metadata->getBenefits()),
            'pBenefits' => $pBenefits,
        ];
    }
}
