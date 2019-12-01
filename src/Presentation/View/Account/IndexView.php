<?php

namespace App\Presentation\View\Account;

use App\Entity\Account;
use App\Form\AccountType;
use App\Presentation\View\AbstractView;

final class IndexView extends AbstractView
{
    /**
     * @inheritDoc
     */
    protected $entityClass = Account::class;

    /**
     * @inheritDoc
     */
    protected $prefixRoute = 'account';

    /**
     * @inheritDoc
     */
    protected $indexFormTypeClass = AccountType::class;

    /**
     * @inheritDoc
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'name'
            ],
            [
                'name'     => 'accountNo',
                'label'    => 'Account No',
                'col_with' => '250',
            ],
            [
                'name'     => 'alias',
                'col_with' => '150',
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
                'search_width' => '242px',
                'buttons'      => [
                    [
                        'type'    => 'primary',
                        'jsClass' => 'js-entity-edit',
                        'icon'    => 'fa fa-pencil-alt',
                    ],
                    [
                        'type'    => 'danger',
                        'jsClass' => 'js-entity-delete',
                        'icon'    => 'fa fa-trash-alt',
                    ],
                ],
            ]
        );
    }
}
