<?php

namespace App\Twig\Parameters;

use App\Entity\Transfer;
use App\Form\TransferType;

/**
 * Represents the transfer parameters used to build index view
 */
class TransferViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = Transfer::class;

    /**
     * {@inheritdoc}
     */
    protected $prefixRoute = 'transfer';

    /**
     * {@inheritdoc}
     */
    protected $newFormTypeClass = TransferType::class;

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'date',
                // To force render the attribute as a date with format
                // @see templates/Components/Macros/render.html.twig
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
                'col_with' => '120',
            ],
            [
                'name' => 'beneficiaryParty',
                'label' => 'Beneficiary',
                'render' => 'account',
            ],
            [
                'name' => 'debtorParty',
                'label' => 'Debtor',
                'render' => 'account',
            ],
            [
                'name' => 'amount',
                'col_with' => '84',
                'render' => 'currency',
            ],
        ];
    }
}
