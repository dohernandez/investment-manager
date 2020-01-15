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
        $price = $stock->getPrice();

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
        if ($price && $price->getPreClose() && $price->getPreClose()->getValue()) {
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
            'dividendYield'        => $metadata ? $metadata->getDividendYield() : null,
            'displayDividendYield' => $displayDividendYield,
            'exDate'               => $exDate,
            'displayToPayDividend' => $displayToPayDividend,
            'toPayDate'            => $toPayDate,
            'peRatio'              => $price ? $price->getPeRatio() : null,
            'preClose'             => $price ? $this->serializer->toArray($price->getPreClose()) : null,
            'open'                 => $price ? $this->serializer->toArray($price->getOpen()) : null,
            'dayLow'               => $price ? $this->serializer->toArray($price->getDayLow()) : null,
            'dayHigh'              => $price ? $this->serializer->toArray($price->getDayHigh()) : null,
            'week52Low'            => $price ? $this->serializer->toArray($price->getWeek52Low()) : null,
            'week52High'           => $price ? $this->serializer->toArray($price->getWeek52High()) : null,
            'change'               => $price ? $this->serializer->toArray($price->getChangePrice()) : null,
            'changePercentage'     => $changePercentage,
            'type'                 => $stock->getType() ? $this->serializer->toArray($stock->getType()) : null,
            'sector'               => $stock->getSector() ? $this->serializer->toArray($stock->getSector()): null,
            'industry'             => $stock->getIndustry() ? $this->serializer->toArray($stock->getIndustry()): null,
            'yahooSymbol'          => $metadata ? $metadata->getYahooSymbol() : null,
            'currency'             => $stock->getCurrency() ? $stock->getCurrency()->getCurrencyCode(): null,
            'notes'                => $stock->getNotes(),
            'title'                => $stock->getTitle(),
        ];
    }
}
