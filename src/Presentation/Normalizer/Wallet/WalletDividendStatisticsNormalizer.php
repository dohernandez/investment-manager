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
        $totalYearProjected = $walletDividendStatistics->getTotalYearProjected();
        $totalLastYearPaid = $walletDividendStatistics->getTotalLastYearPaid();
        $compareLastYear = 0;
        if ($totalYearProjected && $totalLastYearPaid) {
            $compareLastYear = $totalYearProjected->getValue() * 100 / $totalLastYearPaid->getValue() - 100;
        }

        return [
            'dividendYieldProjected' => $walletDividendStatistics->getDividendYieldProjected(),
            'totalYearProjected' => $totalYearProjected ?
                $this->serializer->toArray($totalYearProjected) :
                null,
            'dividendYearMonthsProjected' => $walletDividendStatistics->getDividendYearMonthsProjected() ?
                $this->serializer->toArray($walletDividendStatistics->getDividendYearMonthsProjected()) :
                null,
            'monthYearPaid' => $walletDividendStatistics->getMonthYearPaid() ?
                $this->serializer->toArray($walletDividendStatistics->getMonthYearPaid()) :
                null,
            'yearPaid' => $walletDividendStatistics->getYearPaid() ?
                $this->serializer->toArray($walletDividendStatistics->getYearPaid()) :
                null,
            'totalLastYearPaid' => $totalLastYearPaid ?
                $this->serializer->toArray($totalLastYearPaid) :
                null,
            'totalLastYearMonthsPaid' => $walletDividendStatistics->getDividendLastYearMonthsPaid() ?
                $this->serializer->toArray($walletDividendStatistics->getDividendLastYearMonthsPaid()) :
                null,
            'compareLastYear' => $compareLastYear,
        ];
    }
}
