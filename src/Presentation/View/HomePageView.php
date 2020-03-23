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
                    'movers' => $this->getStockMoversPanel(),
                    'shakers' => $this->getStockShakersPanel(),
                ]
            ] + $context;
    }

    private function getStockMoversPanel()
    {
        return [
            'daily' => [
                'entity_name'  => 'stock_movers_daily',
            ],

            'fields' => [
                [
                    'name' => 'symbol',
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

    private function getStockShakersPanel()
    {
        return [
            'daily' => [
                'entity_name'  => 'stock_shakers_daily',
            ],

            'fields' => [
                [
                    'name' => 'symbol',
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
