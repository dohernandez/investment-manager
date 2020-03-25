<?php

namespace App\Presentation\View;

final class HomePageView
{
    /**
     * Generate the parameters to use when render homepage view.
     *
     * @param array $context
     *
     * @return array
     */
    public function index(array $context = []): array
    {
        return [
                'stock' => [
                    'movers' => $this->getStockDailyPanel('movers'),
                    'shakers' => $this->getStockDailyPanel('shakers'),
                ],
                'market' => [
                    'entity_name'  => 'stock_market'
                ]
            ] + $context;
    }

    private function getStockDailyPanel(string $type)
    {
        return [
            'daily' => [
                'entity_name'  => 'stock_' . $type . '_daily',
            ],

            'fields' => [
                [
                    'name' => 'symbol',
                    'class'    => 'js-manager-table-extra-cell-show',
                ],
                [
                    'name' => 'name',
                    'col_with' => '215',
                    'class'    => 'js-manager-table-extra-cell-hide',
                ],
                [
                    'name' => 'market.symbol',
                    'label' => 'Market',
                ],
                [
                    'name'   => 'value',
                    'render' => 'money',
                ],
                [
                    'name'     => 'displayChange',
                    'label'    => 'Change',
                    'render'   => 'quantity',
                    'quantity' => 'change ? change.value : null',
                ],
            ]
        ];
    }
}
