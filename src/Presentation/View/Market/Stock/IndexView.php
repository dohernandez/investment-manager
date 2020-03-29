<?php

namespace App\Presentation\View\Market\Stock;

use App\Domain\Market\Stock;
use App\Presentation\Form\Market\CreateStockType;
use App\Presentation\Form\Market\CreateOperationType;
use App\Presentation\View\AbstractView;

final class IndexView extends AbstractView
{
    /**
     * @inheritDoc
     */
    protected $entityClass = Stock::class;

    /**
     * @inheritDoc
     */
    protected $prefixRoute = 'market';

    /**
     * @inheritDoc
     */
    protected $createFormTypeClass = CreateStockType::class;

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
                'name' => 'symbol',
            ],
            [
                'name'  => 'market.symbol',
                'label' => 'market',
            ],
            [
                'name'   => 'value',
                'render' => 'money',
            ],
            [
                'name'  => 'displayDividendYield',
                'label' => 'D. Yield',
            ],
            [
                'name'        => 'exDate',
                'label'       => 'Ex. Date',
                'render'      => 'date',
                'date_format' => 'DD/MM/YYYY',
                'class'       => 'js-manager-table-extra-cell-hide',
            ],
            [
                'name'        => 'displayChange',
                'label'       => 'Change',
                'render'      => 'quantity',
                'quantity'    => 'change ? change.value : null',
                'class'       => 'js-manager-table-extra-cell-hide',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function index(array $entities = [], array $context = []): array
    {
        $operationForm = $this->form->create(CreateOperationType::class);

        return parent::index(
            $entities,
            $context +
            [
                'search_width' => '242px',
                'buttons'      => [
                    [
                        'type'    => 'info',
                        'jsClass' => 'js-entity-view',
                        'icon'    => 'fas fa-eye',
                    ],
                    [
                        'type'    => 'warning',
                        'jsClass' => 'js-entity-dividend-yield',
                        'icon'    => 'fas fa-donate',
                    ],
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

                'dividends_fields'  => $this->getDividendsFields(),
                'dividends_buttons' => [
                    [
                        'type'    => 'danger',
                        'jsClass' => 'js-entity-delete',
                        'icon'    => 'fa fa-trash-alt',
                    ],
                ],

                'operation' => [
                    'form' => $operationForm->createView(),
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDividendsFields(): array
    {
        return [
            [
                'name'        => 'exDate',
                'label'       => 'Ex. Date',
                // To force render the attribute as a date with format
                // @see templates/Components/Macros/render.html.twig
                'render'      => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name'        => 'recordDate',
                'label'       => 'Record Date',
                // To force render the attribute as a date with format
                // @see templates/Components/Macros/render.html.twig
                'render'      => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name'        => 'paymentDate',
                'label'       => 'Payment Date',
                // To force render the attribute as a date with format
                // @see templates/Components/Macros/render.html.twig
                'render'      => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name' => 'status',
            ],
            [
                'name'   => 'value',
                'render' => 'money',
            ],
        ];
    }
}
