<?php

namespace App\Presentation\View\Market\Market;

use App\Domain\Market\StockMarket;
use App\Presentation\Form\Market\CreateStockMarketType;
use App\Presentation\View\AbstractView;

final class IndexView extends AbstractView
{
    /**
     * @inheritDoc
     */
    protected $entityClass = StockMarket::class;

    /**
     * @inheritDoc
     */
    protected $prefixRoute = 'market';

    /**
     * @inheritDoc
     */
    protected $createFormTypeClass = CreateStockMarketType::class;

    /**
     * @inheritDoc
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'name',
                'col_with' => '250',
            ],
            [
                'name' => 'countryName',
                'label' => 'country',
            ],
            [
                'name' => 'symbol',
            ],
            [
                'name' => 'currency',
            ],
            [
                'name'   => 'price',
                'render' => 'money',
            ],
            [
                'name'        => 'displayChange',
                'label'       => 'Change',
                'render'      => 'quantity',
                'quantity'    => 'change ? change.value : null',
            ],
            [
                'name' => 'yahooSymbol',
                'label' => 'yahoo symbol',
                'class'       => 'js-manager-table-extra-cell-hide',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function index(array $entities = [], array $context = []): array
    {
        return parent::index(
            $entities,
            $context + [
                'search_width' => '242px',
                'buttons' => [
                    [
                        'type' => 'primary',
                        'jsClass' => 'js-entity-edit',
                        'icon' => 'fa fa-pencil-alt',
                    ],
                    [
                        'type' => 'danger',
                        'jsClass' => 'js-entity-delete',
                        'icon' => 'fa fa-trash-alt',
                    ],
                ],
            ]
        );
    }
}
