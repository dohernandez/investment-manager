<?php

namespace App\Application\Market\Parser;

use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Money;
use DateTime;

final class NasdaqDividendsParser implements DividendsParserInterface
{
    /**
     * @inheritDoc
     */
    public function parser(array $dividends): array
    {
        $stockDividends = [];

        foreach ($dividends as $dividend) {
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
        }

        return $stockDividends;
    }
}
