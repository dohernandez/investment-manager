<?php

namespace App\Twig\Parameters;

use App\Entity\StockDividend;
use App\Form\StockDividendType;

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
    protected $newFormTypeClass = StockDividendType::class;

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'exDate',
                'label' => 'Ex. Date',
                // To force render the attribute as a date with format
                // @see templates/Components/Macros/render.html.twig
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name' => 'paymentDate',
                'label' => 'Payment Date',
                // To force render the attribute as a date with format
                // @see templates/Components/Macros/render.html.twig
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name' => 'recordDate',
                'label' => 'Record Date',
                // To force render the attribute as a date with format
                // @see templates/Components/Macros/render.html.twig
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
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
