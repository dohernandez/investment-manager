<?php

namespace App\Twig\Parameters;

use App\Entity\StockDividend;
use App\Form\StockType;

/**
 * Represents the transfer parameters used to build index view
 */
class StockDividendViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = StockDividend::class;

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
                'name' => 'exDate',
                'label' => 'Ex. Date',
            ],
            [
                'name' => 'paymentDate',
                'label' => 'Payment Date',
            ],
            [
                'name' => 'recordDate',
                'label' => 'Record Date',
            ],
            [
                'name' => 'status',
            ],
            [
                'name' => 'value',
                'render' => 'currency',
            ],
        ];
    }
}
