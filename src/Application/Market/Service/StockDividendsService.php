<?php

namespace App\Application\Market\Service;

use App\Client\DividendsClientInterface;
use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Money;
use DateInterval;
use DateTime;
use Psr\Log\LoggerInterface;

final class StockDividendsService implements StockDividendsServiceInterface
{
    /**
     * @var DividendsClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DividendsClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getStockDividends(Stock $stock): array
    {
        $dividends = $this->client->getDividends($stock->getSymbol());

        /** @var StockDividend $lastDividend */
        $lastDividend = null;

        $stockDividends = [];

        foreach ($dividends as $dividend) {
            try {
                $stockDividend = new StockDividend();
                $stockDividend->setStatus(StockDividend::STATUS_ANNOUNCED);

                // Ex/Eff Date
                $stockDividend->setExDate(new DateTime($dividend['exOrEffDate']));

                // Cash Amount
                $stockDividend->setValue(Money::fromUSDValue(Money::parser($dividend['amount'])));

                // Record Date
                try {
                    $stockDividend->setRecordDate(new DateTime($dividend['recordDate']));
                } catch (\Exception $e) {
                    $stockDividend->setRecordDate($stockDividend->getExDate());
                }

                // Payment Date
                try {
                    $stockDividend->setPaymentDate(new DateTime($dividend['paymentDate']));
                } catch (\Exception $e) {
                    $stockDividend->setPaymentDate($stockDividend->getExDate());
                }

                $now = new DateTime();
                if ($stockDividend->getPaymentDate() < $now) {
                    $stockDividend->setStatus(StockDividend::STATUS_PAYED);
                }

                $stockDividends[] = $stockDividend;

                if (!$lastDividend || $lastDividend->getExDate() < $stockDividend->getExDate()) {
                    $lastDividend = $stockDividend;
                }
            } catch (\Exception $e) {
                $this->logger->debug(
                    'Failed parsing row dividend',
                    [
                        'stock'     => $stock->getSymbol(),
                        'row'       => $dividend,
                        'exception' => $e->getMessage(),
                    ]
                );
            }
        }

        if ($lastDividend !== null) {
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
        }

        return $stockDividends;
    }
}
