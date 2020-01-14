<?php

namespace App\Presentation\Normalizer\Market;

use App\Domain\Market\Stock;
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
final class StockNormalizer implements SubscribingHandlerInterface
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
                'type'      => Stock::class,
                'method'    => 'serializeTransferToJson',
            ],
        ];
    }

    public function serializeTransferToJson(
        JsonSerializationVisitor $visitor,
        Stock $stock,
        array $type,
        Context $context
    ) {
        $metadata = $stock->getMetadata();
        $price = $metadata->getPrice();

        $displayDividendYield = null;
        $exDate = null;

        $nextDividend = $stock->getNextDividend();
        if ($nextDividend) {
            $exDate = $nextDividend->getExDate();

            $displayDividendYield = sprintf(
                '%s (%.2f%%)',
                $nextDividend->getValue(),
                $metadata->getDividendYield()
            );
        }

        $displayToPayDividend = null;
        $toPayDate = null;

        $toPayDividend = $stock->getToPayDividend();
        if ($toPayDividend) {
            $toPayDate = $toPayDividend->getPaymentDate();

            $displayToPayDividend = (string)$toPayDividend->getValue();
        }

        $changePercentage = null;
        if ($price->getPreClose() && $price->getPreClose()->getValue()) {
            $changePercentage = round(
                $price->getChangePrice()->getValue() * 100 / $price->getPreClose()->getValue(),
                3
            );
        }

        return [
            'id'                   => $stock->getId(),
            'name'                 => $stock->getName(),
            'symbol'               => $stock->getSymbol(),
            'value'                => $this->serializer->toArray($stock->getValue()),
            'market'               => $this->serializer->toArray($stock->getMarket()),
            'description'          => $stock->getDescription(),
            'dividendYield'        => $metadata->getDividendYield(),
            'displayDividendYield' => $displayDividendYield,
            'exDate'               => $exDate,
            'displayToPayDividend' => $displayToPayDividend,
            'toPayDate'            => $toPayDate,
            'peRatio'              => $price->getPeRatio(),
            'preClose'             => $this->serializer->toArray($price->getPreClose()),
            'open'                 => $this->serializer->toArray($price->getOpen()),
            'dayLow'               => $this->serializer->toArray($price->getDayLow()),
            'dayHigh'              => $this->serializer->toArray($price->getDayHigh()),
            'week52Low'            => $this->serializer->toArray($price->getWeek52Low()),
            'week52High'           => $this->serializer->toArray($price->getWeek52High()),
            'change'               => $this->serializer->toArray($price->getChangePrice()),
            'changePercentage'     => $changePercentage,
            'type'                 => $this->serializer->toArray($stock->getType()),
            'sector'               => $this->serializer->toArray($stock->getSector()),
            'industry'             => $this->serializer->toArray($stock->getIndustry()),
            'yahooSymbol'          => $metadata->getYahooSymbol(),
            'currency'             => $stock->getCurrency()->getCurrencyCode(),
            'notes'                => $stock->getNotes(),
            'title'                => $stock->getTitle(),
        ];
    }
}
