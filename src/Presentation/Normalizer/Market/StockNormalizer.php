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

        $displayChange = $price && $price->getChangePrice() ? sprintf(
            '%s (%.2f%%)',
            $price->getChangePrice(),
            $price->getChangePercentage()
        ) : null;

        $data = $price ? $price->getData() : null;

        return [
            'id'                   => $stock->getId(),
            'name'                 => $stock->getName(),
            'symbol'               => $stock->getSymbol(),
            'value'                => $price ? $this->serializer->toArray($price->getPrice()) : null,
            'market'               => $this->serializer->toArray($stock->getMarket()),
            'description'          => $stock->getDescription(),
            'dividendYield'        => $metadata ? $metadata->getDividendYield() : null,
            'displayDividendYield' => $displayDividendYield,
            'exDate'               => $exDate ? $exDate->format('c') : null,
            'displayToPayDividend' => $displayToPayDividend,
            'toPayDate'            => $toPayDate ? $toPayDate->format('c') : null,
            'peRatio'              => $price ? $price->getPeRatio() : null,
            'preClose'             => $price ? $this->serializer->toArray($price->getPreClose()) : null,
            'open'                 => $data ? $this->serializer->toArray($data->getOpen()) : null,
            'close'                => $data && $data->getClose() ? $this->serializer->toArray($data->getClose()) : null,
            'dayLow'               => $data ? $this->serializer->toArray($data->getDayLow()) : null,
            'dayHigh'              => $data ? $this->serializer->toArray($data->getDayHigh()) : null,
            'weekLow'              => $data && $data->getWeekLow() ?
                $this->serializer->toArray($data->getWeekLow()) :
                null,
            'weekHigh'             => $data && $data->getWeekHigh() ?
                $this->serializer->toArray(
                    $data->getWeekHigh()
                ) :
                null,
            'week52Low'            => $price ? $this->serializer->toArray($price->getWeek52Low()) : null,
            'week52High'           => $price ? $this->serializer->toArray($price->getWeek52High()) : null,
            'change'               => $price ? $this->serializer->toArray($price->getChangePrice()) : null,
            'priceUpdatedAt'       => $price ? $price->getUpdatedAt()->format('c') : null,
            'changePercentage'     => $price ? $price->getChangePercentage() : null,
            'displayChange'        => $displayChange,
            'type'                 => $stock->getType() ? $this->serializer->toArray($stock->getType()) : null,
            'sector'               => $stock->getSector() ? $this->serializer->toArray($stock->getSector()) : null,
            'industry'             => $stock->getIndustry() ? $this->serializer->toArray($stock->getIndustry()) : null,
            'yahooSymbol'          => $metadata ? $metadata->getYahooSymbol() : null,
            'currency'             => $stock->getCurrency() ? $stock->getCurrency()->getCurrencyCode() : null,
            'notes'                => $stock->getNotes(),
            'delisted'             => $stock->isDelisted(),
            'delistedAt'           => $stock->getDelistedAt() ? $stock->getDelistedAt()->format('c') : null,
            'title'                => $stock->getTitle(),
            'dividendFrequency'    => $stock->getMetadata()->getDividendFrequency(),
        ];
    }
}
