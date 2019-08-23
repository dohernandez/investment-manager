<?php

namespace App\Twig\Parameters;

use App\Entity\StockMarket;
use App\Form\StockMarketType;

/**
 * Represents the transfer parameters used to build index view
 */
class StockMarketViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = StockMarket::class;

    /**
     * {@inheritdoc}
     */
    protected $prefixRoute = 'transfer';

    /**
     * {@inheritdoc}
     */
    protected $newFormTypeClass = StockMarketType::class;

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'name',
                'col_with' => '350',
            ],
            [
                'name' => 'countryName',
                'label' => 'country',
            ],
            [
                'name' => 'symbol',
            ],
        ];
    }

    public function index(array $entities = [], array $context = []): array
    {
        return parent::index($entities, $context + [
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
            ]);
    }
}
