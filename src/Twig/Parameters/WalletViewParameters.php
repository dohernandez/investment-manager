<?php

namespace App\Twig\Parameters;

use App\Entity\Wallet;
use App\Form\WalletType;

/**
 * Represents the account parameters used to build index view
 */
class WalletViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = Wallet::class;

    /**
     * {@inheritdoc}
     */
    protected $prefixRoute = 'account';

    /**
     * {@inheritdoc}
     */
    protected $newFormTypeClass = WalletType::class;

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
                'name' => 'invested',
                'render' => 'currency',
            ],
            [
                'name' => 'capital',
                'render' => 'currency',
            ],
            [
                'name' => 'funds',
                'render' => 'currency',
            ],
            [
                'name' => 'broker',
                'render' => 'broker',
            ],
        ];
    }
}
