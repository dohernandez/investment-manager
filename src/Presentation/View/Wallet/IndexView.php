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
                'name'   => 'metadata.invested',
                'label'   => 'Invested',
                'render' => 'money',
            ],
            [
                'name'   => 'metadata.capital',
                'label'   => 'Capital',
                'render' => 'money',
            ],
            [
                'name'   => 'metadata.funds',
                'label'   => 'Funds',
                'render' => 'money',
            ],
            [
                'name'   => 'metadata.pBenefits',
                'label'  => 'Benefits',
                'render' => 'percentage',
            ],
            [
                'name'   => 'broker',
                'label'   => 'Broker',
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
                    [
                        'type'    => 'primary',
                        'jsClass' => 'js-entity-edit',
                        'icon'    => 'fa fa-pencil-alt',
                    ],
                ],
            ]
        );
    }
}
