<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Domain\Wallet\Wallet;
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
final class WalletNormalizer implements SubscribingHandlerInterface
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
                'type'      => Wallet::class,
                'method'    => 'serializeWalletToJson',
            ],
        ];
    }

    public function serializeWalletToJson(JsonSerializationVisitor $visitor, Wallet $wallet, array $type, Context $context)
    {
        $book = $wallet->getBook();

        $displayBenefits = sprintf(
            '%s (%.2f%%)',
            $book->getBenefits(),
            $book->getPercentageBenefits()
        );

        return [
            'id' => $wallet->getId(),
            'name' => $wallet->getName(),
            'broker' => $this->serializer->toArray($wallet->getBroker()),
            'book' => $this->serializer->toArray($wallet->getBook()),
            'displayBenefits' => $displayBenefits,
            'title' => $wallet->getTitle(),
        ];
    }
}
