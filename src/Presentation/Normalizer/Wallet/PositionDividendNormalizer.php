<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Application\Wallet\Handler\Output\PositionDividend;
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
final class PositionDividendNormalizer implements SubscribingHandlerInterface
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
                'type'      => PositionDividend::class,
                'method'    => 'serializePositionDividendToJson',
            ],
        ];
    }

    public function serializePositionDividendToJson(
        JsonSerializationVisitor $visitor,
        PositionDividend $positionDividend,
        array $type,
        Context $context
    ) {
        return [
            'id'                       => $positionDividend->getId(),
            'stock'                    => $this->serializer->toArray($positionDividend->getStock()),
            'invested'                 => $this->serializer->toArray($positionDividend->getInvested()),
            'amount'                   => $positionDividend->getAmount(),
            'exDate'                   => $positionDividend->getExDate() ?
                $positionDividend->getExDate()->format('c') :
                null,
            'displayDividendYield'     => $positionDividend->getDisplayDividendYield(),
            'realDisplayDividendYield' => $positionDividend->getRealDisplayDividendYield(),
            'dividendRetention'        => $positionDividend->getDividendRetention() ?
                $this->serializer->toArray($positionDividend->getDividendRetention()) :
                null,
            'title'                    => $positionDividend->getTitle(),
        ];
    }
}
