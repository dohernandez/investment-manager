<?php

namespace App\Service;

use App\Application\Market\Client\DividendsClientInterface;
use App\Entity\Stock;
use App\Entity\StockDividend;
use App\VO\Money;
use Psr\Log\LoggerInterface;

class StockDividendsService
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
                $stockDividend->setExDate(new \DateTimeImmutable($dividend['exOrEffDate']));

                // Cash Amount
                $stockDividend->setValue(Money::fromUSDValue($this->parserPrice($dividend['amount'])));

                // Record Date
                $stockDividend->setRecordDate(new \DateTimeImmutable($dividend['recordDate']));

                // Payment Date
                $stockDividend->setPaymentDate(new \DateTimeImmutable($dividend['paymentDate']));

                $now = new \DateTimeImmutable();
                if ($stockDividend->getPaymentDate() < $now) {
                    $stockDividend->setStatus(StockDividend::STATUS_PAYED);
                }

                $stockDividends[] = $stockDividend;

                if (!$lastDividend || $lastDividend->getExDate() < $stockDividend->getExDate()) {
                    $lastDividend = $stockDividend;
                }
            } catch (\Exception $e) {
                $this->logger->debug('Failed parsing row dividend', [
                    'stock' => $stock->getSymbol(),
                    'row' => $dividend,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        if ($lastDividend !== null) {
            // Adding projected dividend until the end of the year.
            $now = new \DateTimeImmutable();
            $year = $now->add(\DateInterval::createFromDateString('1 year'));

            if ($lastDividend->getExDate() > $now->add(\DateInterval::createFromDateString('-3 months'))) {
                $exDate = (clone $lastDividend->getExDate())
                    ->add(\DateInterval::createFromDateString('3 months'));

                while ($year >= $exDate) {
                    $nextDividend = new StockDividend();
                    $nextDividend
                        ->setStatus(StockDividend::STATUS_PROJECTED)
                        ->setExDate($exDate)
                        ->setValue(clone $lastDividend->getValue());

                    $stock->addDividend($nextDividend);

                    $exDate = (clone $exDate)
                        ->add(\DateInterval::createFromDateString('3 months'));
                }
            }
        }

        return $stockDividends;
    }

    private function parserPrice(string $price, int $divisor = 100): int
    {
        $price = str_replace('$', '', $price);

        return floatval($price) * $divisor;
    }
}
