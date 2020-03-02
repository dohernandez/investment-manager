<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Domain\Wallet\Position;
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
final class PositionNormalizer implements SubscribingHandlerInterface
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
                'type'      => Position::class,
                'method'    => 'serializePositionToJson',
            ],
        ];
    }

    public function serializePositionToJson(JsonSerializationVisitor $visitor, Position $position, array $type, Context $context)
    {
        $book = $position->getBook();
        $dividend = $book->getDividendPaid() ? $position->getBook()->getDividendPaid()->getTotal() : null;
        $displayBenefits = sprintf(
            '%s (%.2f%%)',
            $book->getBenefits(),
            $book->getPercentageBenefits()
        );
        $displayChange = $book->getChanged() ? sprintf(
            '%s (%.2f%%)',
            $book->getChanged(),
            $book->getChanged()->getValue() * 100 / $book->getPreClosed()->getValue()
        ) : null;

        return [
            'id' => $position->getId(),
            'openedAt' => $position->getOpenedAt()->format('c'),
            'stock' => $this->serializer->toArray($position->getStock()),
            'amount' => $position->getAmount(),
            'capital' => $this->serializer->toArray($position->getCapital()),
            'invested' => $position->getInvested() ? $this->serializer->toArray($position->getInvested()) : null,
            'dividend' => $dividend ? $this->serializer->toArray($dividend) : null,
            'displayBenefits' => $displayBenefits,
            'benefits' => $this->serializer->toArray($book->getBenefits()),
            'displayChange' => $displayChange,
            'change' => $book->getChanged() ? $this->serializer->toArray($book->getChanged()): null,
            'title' => $position->getTitle(),
        ];
    }
}
