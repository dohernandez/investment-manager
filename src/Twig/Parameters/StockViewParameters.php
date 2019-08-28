<?php

namespace App\Twig\Parameters;

use App\Entity\Stock;
use App\Form\StockType;

/**
 * Represents the transfer parameters used to build index view
 */
class StockViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = Stock::class;

    /**
     * {@inheritdoc}
     */
    protected $newFormTypeClass = StockType::class;

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'name'
            ],
            [
                'name' => 'symbol',
            ],
            [
                'name' => 'market.symbol',
                'label' => 'market',
            ],
            [
                'name' => 'value',
                'render' => 'money',
            ],
            [
                'name' => 'displayDividendYield',
                'label' => 'D. Yield',
            ],
            [
                'name' => 'exDate',
                'label' => 'Ex. Date',
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY',
                'class' => 'js-manager-table-extra-cell-hide',
            ],
        ];
    }

    public function index(array $entities = [], array $context = []): array
    {
        return parent::index($entities, $context + [
                'search_width' => '242px',
                'buttons' => [
                    [
                        'type' => 'info',
                        'jsClass' => 'js-entity-view',
                        'icon' => 'fas fa-eye',
                    ],
                    [
                        'type' => 'warning',
                        'jsClass' => 'js-entity-edit-dividend-yield',
                        'icon' => 'fas fa-donate',
                    ],
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
            ]);
    }
}
