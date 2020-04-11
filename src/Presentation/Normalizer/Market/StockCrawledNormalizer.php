<?php

namespace App\Presentation\Normalizer\Market;

use App\Application\Market\Scraper\StockCrawled;
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
final class StockCrawledNormalizer implements SubscribingHandlerInterface
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
                'type'      => StockCrawled::class,
                'method'    => 'serializeTransferToJson',
            ],
        ];
    }

    public function serializeTransferToJson(
        JsonSerializationVisitor $visitor,
        StockCrawled $stock,
        array $type,
        Context $context
    ) {
        return [
            'id'                   => null,
            'name'                 => $stock->getName(),
            'symbol'               => $stock->getSymbol(),
            'value'                => $this->serializer->toArray($stock->getPrice()),
            'market'               => $this->serializer->toArray($stock->getMarket()),
            'description'          => $stock->getDescription(),
            'dividendYield'        => null,
            'displayDividendYield' => null,
            'exDate'               => null,
            'displayToPayDividend' => null,
            'toPayDate'            => null,
            'peRatio'              => $stock->getPeRatio(),
            'preClose'             => $this->serializer->toArray($stock->getPreClose()),
            'open'                 => $this->serializer->toArray($stock->getData()),
            'dayLow'               => $this->serializer->toArray($stock->getData()->getDayLow()),
            'dayHigh'              => $this->serializer->toArray($stock->getData()->getDayHigh()),
            'week52Low'            => $this->serializer->toArray($stock->getWeek52Low()),
            'week52High'           => $this->serializer->toArray($stock->getWeek52High()),
            'change'               => $this->serializer->toArray($stock->getChangePrice()),
            'changePercentage'     => null,
            'type'                 => $stock->getType() ? $this->serializer->toArray($stock->getType()) : null,
            'sector'               => $stock->getSector() ? $this->serializer->toArray($stock->getSector()) : null,
            'industry'             => $stock->getIndustry() ? $this->serializer->toArray($stock->getIndustry()) : null,
            'yahooSymbol'          => $stock->getYahooSymbol(),
            'currency'             => $stock->getCurrency()->getCurrencyCode(),
            'notes'                => null,
            'title'                => null,
        ];
    }
}
