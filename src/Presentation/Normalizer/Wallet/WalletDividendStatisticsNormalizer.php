<?php

namespace App\Presentation\Normalizer\Wallet;

use App\Application\Wallet\Handler\Output\WalletDividendStatistics;
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
final class WalletDividendStatisticsNormalizer implements SubscribingHandlerInterface
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
                'type'      => WalletDividendStatistics::class,
                'method'    => 'serializeWalletStatisticsToJson',
            ],
        ];
    }

    public function serializeWalletStatisticsToJson(JsonSerializationVisitor $visitor, WalletDividendStatistics $walletDividendStatistics, array $type, Context $context)
    {
        $dividendLastYearPaid = $walletDividendStatistics->getDividendLastYearPaid();

        $yearProjected = $walletDividendStatistics->getYearProjected();
        $compareLastYearProjected = 0;
        if ($yearProjected && $dividendLastYearPaid) {
            $compareLastYearProjected = $yearProjected->getValue() * 100 / $dividendLastYearPaid->getValue() - 100;
        }

        $yearPaid = $walletDividendStatistics->getYearPaid();
        $compareLastYearPaid = 0;
        if ($yearPaid && $dividendLastYearPaid) {
            $compareLastYearPaid = $yearPaid->getValue() * 100 / $dividendLastYearPaid->getValue() - 100;
        }

        return [
            'dividendYieldProjected' => $walletDividendStatistics->getDividendYieldProjected(),
            'dividendYieldPaid' => $walletDividendStatistics->getDividendYieldPaid(),
            'yearProjected' => $yearProjected ?
                $this->serializer->toArray($yearProjected) :
                null,
            'dividendYearMonthsProjected' => $walletDividendStatistics->getDividendYearMonthsProjected() ?
                $this->serializer->toArray($walletDividendStatistics->getDividendYearMonthsProjected()) :
                null,
            'monthPaid' => $walletDividendStatistics->getMonthPaid() ?
                $this->serializer->toArray($walletDividendStatistics->getMonthPaid()) :
                null,
            'yearPaid' => $yearPaid ?
                $this->serializer->toArray($yearPaid) :
                null,
            'dividendYearMonthsPaid' => $walletDividendStatistics->getDividendYearMonthsPaid() ?
                $this->serializer->toArray($walletDividendStatistics->getDividendYearMonthsPaid()) :
                null,
            'dividendLastYearMonthsPaid' => $walletDividendStatistics->getDividendLastYearMonthsPaid() ?
                $this->serializer->toArray($walletDividendStatistics->getDividendLastYearMonthsPaid()) :
                null,
            'dividendLastYearPaid' => $dividendLastYearPaid ?
                $this->serializer->toArray($dividendLastYearPaid) :
                null,
            'compareLastYearProjected' => $compareLastYearProjected,
            'compareLastYearPaid' => $compareLastYearPaid,
        ];
    }
}
