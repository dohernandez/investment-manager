<?php

namespace App\Twig\Parameters;

use App\Entity\Entity;
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
                'name' => 'pBenefits',
                'label' => 'Benefits',
                'render' => 'percentage',
            ],
            [
                'name' => 'broker',
                'render' => 'broker',
            ],
        ];
    }

    public function index(array $entities = [], array $context = []): array
    {
        return parent::index($entities, $context + [
            'buttons' => [
                [
                    'type' => 'info',
                    'jsClass' => 'js-entity-dashboard-yield',
                    'icon' => 'fas fa-book-open',
                ],
            ],
        ]);
    }

    /**
     * @param Wallet $wallet
     * @param array $context
     *
     * @return array
     */
    public function dashboard(Wallet $wallet, array $context = []): array
    {
        return [
                'wallet' => $wallet,
                'entity_name' => trim(implode(' ', preg_split('/(?=[A-Z])/', $this->getEntityName()))),
            ] + $context;
    }

}
