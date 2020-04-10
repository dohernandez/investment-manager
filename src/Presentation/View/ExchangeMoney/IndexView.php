<?php

namespace App\Presentation\View\ExchangeMoney;

use App\Infrastructure\Money\Currency;

final class IndexView
{
    /**
     * @inheritDoc
     */
    public function index(array $entities = [], array $context = []): array
    {
        return $context + [
                'rates' => $entities,
                'bgColors'  => [
                    Currency::CURRENCY_CODE_CAD => 'orange',
                    Currency::CURRENCY_CODE_USD => 'green',
                    Currency::CURRENCY_CODE_EUR => 'blue',
                ],
            ];
    }
}
