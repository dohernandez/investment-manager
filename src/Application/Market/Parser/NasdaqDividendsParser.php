<?php

namespace App\Application\Market\Parser;

use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;
use Exception;

final class NasdaqDividendsParser implements DividendsParserInterface
{
    /**
     * @inheritDoc
     */
    public function parser(Currency $currency, array $dividends): array
    {
        $stockDividends = [];

        foreach ($dividends as $dividend) {
            $stockDividend = new StockDividend();
            $stockDividend->setStatus(StockDividend::STATUS_ANNOUNCED);

            // Ex/Eff Date
            try {
                $stockDividend->setExDate(new DateTime($dividend['exOrEffDate']));
            } catch (Exception $e) {
                continue;
            }

            // Cash Amount
            $stockDividend->setValue(new Money($currency, Money::parser($dividend['amount'])));

            // Record Date
            try {
                $stockDividend->setRecordDate(new DateTime($dividend['recordDate']));
            } catch (Exception $e) {
                $stockDividend->setRecordDate($stockDividend->getExDate());
            }

            // Payment Date
            try {
                $stockDividend->setPaymentDate(new DateTime($dividend['paymentDate']));
            } catch (Exception $e) {
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
