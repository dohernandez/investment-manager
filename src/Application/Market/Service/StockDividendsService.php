<?php

namespace App\Application\Market\Service;

use App\Application\Market\Parser\DividendsParserInterface;
use App\Application\Market\Client\DividendsClientInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use DateInterval;
use DateTime;

final class StockDividendsService implements StockDividendsServiceInterface
{
    /**
     * @var DividendsClientInterface
     */
    private $client;

    /**
     * @var DividendsParserInterface
     */
    private $parser;

    public function __construct(
        DividendsClientInterface $client,
        DividendsParserInterface $parser
    ) {
        $this->client = $client;
        $this->parser = $parser;
    }

    /**
     * @inheritDoc
     */
    public function getStockDividends(Stock $stock): array
    {
        $dividends = $this->client->getDividends($stock->getSymbol());

        $stockDividends = $this->parser->parser($stock->getCurrency(), $dividends);

        /** @var StockDividend $lastDividend */
        $lastDividend = null;

        foreach ($stockDividends as $stockDividend) {
            if (!$lastDividend || $lastDividend->getExDate() < $stockDividend->getExDate()) {
                $lastDividend = $stockDividend;
            }
        }

        if ($lastDividend === null) {
            return $stockDividends;
        }

        if ($frequency = $stock->getMetadata()->getDividendFrequency()) {
            // Adding projected dividend until the end of the year.
            $now = new DateTime();
            $year = (clone $now)->add(DateInterval::createFromDateString('1 year'));

            if ($lastDividend->getExDate() > $now->sub(DateInterval::createFromDateString($frequency))) {
                $exDate = (clone $lastDividend->getExDate())
                    ->add(DateInterval::createFromDateString($frequency));

                while ($year >= $exDate) {
                    $nextDividend = (new StockDividend())
                        ->setStatus(StockDividend::STATUS_PROJECTED)
                        ->setExDate($exDate)
                        ->setValue(clone $lastDividend->getValue());

                    $stockDividends[] = $nextDividend;

                    $exDate = (clone $exDate)
                        ->add(DateInterval::createFromDateString($frequency));
                }
            }
        }


        return $stockDividends;
    }
}
