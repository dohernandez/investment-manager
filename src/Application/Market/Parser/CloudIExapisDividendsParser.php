<?php

namespace App\Application\Market\Parser;

use App\Domain\Market\StockDividend;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;

final class CloudIExapisDividendsParser implements DividendsParserInterface
{
    /**
     * @inheritDoc
     */
    public function parser(Currency $currency, array $dividends): array
    {
        $stockDividends = [];

        foreach ($dividends as $dividend) {
            if (empty($dividend)) {
                continue;
            }

            $stockDividend = new StockDividend();
            $stockDividend->setStatus(StockDividend::STATUS_ANNOUNCED);

            // Ex/Eff Date
            $stockDividend->setExDate(new DateTime($dividend['exDate']));

            // Cash Amount
            $stockDividend->setValue(new Money($currency, Money::parser($dividend['amount'])));

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
