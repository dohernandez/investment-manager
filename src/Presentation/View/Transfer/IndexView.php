<?php

namespace App\Presentation\View\Transfer;

use App\Entity\Transfer;
use App\Presentation\Form\Transfer\CreateTransferType;
use App\Presentation\View\AbstractView;

final class IndexView extends AbstractView
{
    /**
     * @inheritDoc
     */
    protected $entityClass = Transfer::class;

    /**
     * @inheritDoc
     */
    protected $prefixRoute = 'transfer';

    /**
     * @inheritDoc
     */
    protected $createFormTypeClass = CreateTransferType::class;

    /**
     * @inheritDoc
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
                'name' => 'beneficiary',
                'label' => 'Beneficiary',
                'render' => 'account',
            ],
            [
                'name' => 'debtor',
                'label' => 'Debtor',
                'render' => 'account',
            ],
            [
                'name' => 'amount',
                'col_with' => '84',
                'render' => 'money',
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
