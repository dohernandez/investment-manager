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
    protected $prefixRoute = 'transfer';

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
        ];
    }
}
