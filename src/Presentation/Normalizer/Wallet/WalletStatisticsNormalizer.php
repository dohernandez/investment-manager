<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Application\Wallet\Handler\Output\WalletStatistics;
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
final class WalletStatisticsNormalizer implements SubscribingHandlerInterface
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
                'type'      => WalletStatistics::class,
                'method'    => 'serializeWalletStatisticsToJson',
            ],
        ];
    }

    public function serializeWalletStatisticsToJson(JsonSerializationVisitor $visitor, WalletStatistics $walletStatistics, array $type, Context $context)
    {
        return [
            'invested' => $this->serializer->toArray($walletStatistics->getInvested()),
            'capital' => $this->serializer->toArray($walletStatistics->getCapital()),
            'netCapital' => $this->serializer->toArray($walletStatistics->getNetCapital()),
            'funds' => $this->serializer->toArray($walletStatistics->getFunds()),
            'dividends' => $walletStatistics->getDividends() ?
                $this->serializer->toArray($walletStatistics->getDividends()) :
                null,
            'commissions' => $walletStatistics->getCommissions() ?
                $this->serializer->toArray($walletStatistics->getCommissions()) :
                null,
            'connection' => $walletStatistics->getConnection() ?
                $this->serializer->toArray($walletStatistics->getConnection()) :
                null,
            'interest' => $walletStatistics->getInterest() ?
                $this->serializer->toArray($walletStatistics->getInterest()) :
                null,
            'benefits' => $walletStatistics->getBenefits() ?
                $this->serializer->toArray($walletStatistics->getBenefits()) :
                null,
            'percentageBenefits' => $walletStatistics->getPercentageBenefits(),
        ];
    }
}
