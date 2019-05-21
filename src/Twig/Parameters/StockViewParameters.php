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
}
