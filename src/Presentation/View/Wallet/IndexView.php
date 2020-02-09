<?php

namespace App\Presentation\View\Wallet;

use App\Domain\Wallet\Wallet;
use App\Presentation\Form\Wallet\CreateWalletType;
use App\Presentation\View\AbstractView;

final class IndexView extends AbstractView
{
    /**
     * @inheritDoc
     */
    protected $entityClass = Wallet::class;

    /**
     * @inheritDoc
     */
    protected $prefixRoute = 'wallet';

    /**
     * @inheritDoc
     */
    protected $createFormTypeClass = CreateWalletType::class;

    /**
     * @inheritDoc
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'name',
            ],
            [
                'name'   => 'invested',
                'render' => 'money',
            ],
            [
                'name'   => 'capital',
                'render' => 'money',
            ],
            [
                'name'   => 'funds',
                'render' => 'money',
            ],
            [
                'name'   => 'pBenefits',
                'label'  => 'Benefits',
                'render' => 'percentage',
            ],
            [
                'name'   => 'broker',
                'render' => 'broker',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function index(array $entities = [], array $context = []): array
    {
        return parent::index(
            $entities,
            $context + [
                'buttons' => [
                    [
                        'type'    => 'info',
                        'jsClass' => 'js-entity-dashboard-yield',
                        'icon'    => 'fas fa-book-open',
                    ],
                ],
            ]
        );
    }
}
