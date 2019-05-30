<?php

namespace App\Twig\Parameters;

use App\Entity\Broker;
use App\Form\BrokerType;

/**
 * Represents the transfer parameters used to build index view
 */
class BrokerViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = Broker::class;

    /**
     * {@inheritdoc}
     */
    protected $newFormTypeClass = BrokerType::class;

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'name',
            ],
            [
                'name' => 'site',
            ],
            [
                'name' => 'account',
                'render' => 'account',
            ],
        ];
    }

    public function index(array $entities = [], array $context = []): array
    {
        return parent::index($entities, $context + [
                'buttons' => [
                    [
                        'type' => 'warning',
                        'jsClass' => 'js-entity-edit-broker-stocks',
                        'icon' => 'fas fa-money-bill',
                    ],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getStockFields(): array
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
                'label' => 'Market',
            ],
            [
                'name' => 'value',
                'render' => 'currency',
            ],
            [
                'name' => 'dividendYield',
                'label' => 'D. Yield',
                'render' => 'percentage',
            ],
            [
                'name' => 'exDate',
                'label' => 'Ex. Date',
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY',
                'class' => 'js-manager-table-extra-cell',
            ],
        ];
    }

    public function stocks(Broker $broker, array $context = []): array
    {
        return [
                'broker' => $broker,
                'fields' => $this->getStockFields(),
                'entity_name' => 'Broker Stock',
            ] + $context + [
                'buttons' => [
                    [
                        'type' => 'warning',
                        'jsClass' => 'js-entity-edit-dividend-yield',
                        'icon' => 'fas fa-donate',
                    ],
                ],
                'create_button_label' => 'Add stock',
            ];
    }
}
