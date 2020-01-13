<?php

namespace App\Presentation\Normalizer;

use App\Domain\Account\Account;
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
 * @package App\Presentation\Normalizer
 */
final class AccountNormalizer implements SubscribingHandlerInterface
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
                'type'      => Account::class,
                'method'    => 'serializeAccountToJson',
            ],
        ];
    }

    public function serializeAccountToJson(JsonSerializationVisitor $visitor, Account $account, array $type, Context $context)
    {
        return [
            'id' => $account->getId(),
            'name' => $account->getName(),
            'accountNo' => $account->getAccountNo(),
            'alias' => $account->getName(),
            'title' => $account->getTitle(),
            'balance' => $this->serializer->toArray($account->getBalance()),
        ];
    }
}
