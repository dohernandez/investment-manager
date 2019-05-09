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
}
