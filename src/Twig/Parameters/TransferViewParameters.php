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
                'name' => '__toString',
                'label' => 'Transaction',
            ],
        ];
    }
}
