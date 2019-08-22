<?php

namespace App\Twig\Parameters;

use App\Entity\Account;
use App\Form\AccountType;

/**
 * Represents the account parameters used to build index view
 */
class AccountViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = Account::class;

    /**
     * {@inheritdoc}
     */
    protected $prefixRoute = 'account';

    /**
     * {@inheritdoc}
     */
    protected $newFormTypeClass = AccountType::class;

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array
    {
        return [
            [
                'name' => 'name'
            ],
            [
                'name' => 'accountNo',
                'label' => 'Account No',
                'col_with' => '250',
            ],
            [
                'name' => 'alias',
                'col_with' => '150',
            ],
        ];
    }

    public function index(array $entities = [], array $context = []): array
    {
        return parent::index($entities, $context + [
                'search_width' => '242px',
                'buttons' => [
                    [
                        'type' => 'primary',
                        'jsClass' => 'js-entity-edit',
                        'icon' => 'fa fa-pencil-alt',
                    ],
                ],
            ]);
    }
}
