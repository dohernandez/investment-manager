<?php

namespace App\Presentation\View\Account;

use App\Entity\Account;
use App\Presentation\Form\Account\CreateAccountType;
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
    protected $indexFormTypeClass = CreateAccountType::class;

    /**
     * @inheritDoc
     */
    protected function getFields(): array
    {
        return [
            [
                'name'     => 'name',
                'col_with' => '350',
            ],
            [
                'name'  => 'accountNo',
                'label' => 'Account No',
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
