<?php

namespace App\Presentation\Normalizer\Transfer;

use App\Domain\Transfer\Account;
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
final class AccountNormalizer implements SubscribingHandlerInterface
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
            'title' => $account->getTitle(),
        ];
    }
}
