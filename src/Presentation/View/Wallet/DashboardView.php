<?php

namespace App\Presentation\View\Wallet;

use App\Presentation\Form\Wallet\CreateOperationType;
use App\Presentation\Form\Wallet\StockNoteType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class DashboardView
{
    /**
     * @var FormFactoryInterface
     */
    private $form;

    public function __construct(FormFactoryInterface $form)
    {
        $this->form = $form;
    }

    /**
     * Generate the parameters to use when render dashboard view.
     *
     * @param string $id
     * @param array $context
     *
     * @return array
     */
    public function index(string $id, array $context = []): array
    {
        $operationForm = $this->form->create(CreateOperationType::class);

        return [
                'wallet_id' => $id,
                'position' => $this->getPositionPanel($operationForm),
                'operation' => $this->getOperationPanel($operationForm),
            ] + [
                'page_title' => 'Wallets',
            ] + $context;
    }

    /**
     * Generate the parameters to use when render panel operation in the dashboard view.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getOperationPanel(FormInterface $form): array
    {
        return [
            'entity_name'  => 'operation',
            'search_width' => '235px',
            'form'         => $form->createView(),

            'fields' => [
                [
                    'name'        => 'dateAt',
                    'label'       => 'Date',
                    'render'      => 'date',
                    'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
                ],
                [
                    'name' => 'type',
                ],
                [
                    'name'   => 'stock.name',
                    'label'  => 'Stock',
                    'render' => 'check',
                    'check'  => 'stock',
                ],
                [
                    'name'   => 'stock.symbol',
                    'label'  => 'Symbol',
                    'render' => 'check',
                    'check'  => 'stock',
                ],
                [
                    'name'   => 'stock.market.symbol',
                    'label'  => 'Market',
                    'render' => 'check',
                    'check'  => 'stock',
                ],
                [
                    'name'   => 'price',
                    'render' => 'money',
                    'class'  => 'js-manager-table-extra-cell-hide',
                ],
                [
                    'name'  => 'amount',
                    'label' => 'Amt',
                ],
                [
                    'name'   => 'value',
                    'render' => 'money',
                ],
                [
                    'name'     => 'commissions',
                    'render'   => 'money',
                    'col_with' => '120',
                ],
            ]
        ];
    }

    /**
     * Generate the parameters to use when render panel operation in the dashboard view.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getPositionPanel(FormInterface $form): array
    {
        $stockNoteForm = $this->form->create(StockNoteType::class);

        return [
            'entity_name'  => 'position',
            'search_width' => '235px',
            'form'         => $form->createView(),

            'fields' => [
                [
                    'name'        => 'openedAt',
                    'label'       => 'Added Date',
                    'render'      => 'date',
                    'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
                    'class'       => 'js-manager-table-extra-cell-hide',
                ],
                [
                    'name'     => 'stock.name',
                    'label'    => 'Stock',
                    'col_with' => '264',
                    'class'    => 'js-manager-table-extra-cell-show',
                ],
                [
                    'name'  => 'stock.symbol',
                    'label' => 'Symbol',
                ],
                [
                    'name'  => 'stock.market.symbol',
                    'label' => 'Market',
                ],
                [
                    'name'   => 'stock.price',
                    'label'  => 'Price',
                    'render' => 'money',
                    'class'  => 'js-manager-table-extra-cell-hide',
                ],
                [
                    'name'  => 'amount',
                    'label' => 'Amt',
                ],
                [
                    'name'   => 'capital',
                    'render' => 'money',
                ],
                [
                    'name'   => 'invested',
                    'render' => 'money',
                ],
                [
                    'name'   => 'dividend',
                    'render' => 'money',
                ],
                [
                    'name'     => 'displayBenefits',
                    'label'    => 'benefits',
                    'render'   => 'quantity',
                    'quantity' => 'benefits.value',
                    'class'    => 'js-manager-table-extra-cell-hide',
                ],
                [
                    'name'     => 'displayChange',
                    'label'    => 'Change',
                    'render'   => 'quantity',
                    'quantity' => 'change ? change.value : null',
                    'class'    => 'js-manager-table-extra-cell-hide',
                ],
            ],

            'buttons' => [],

            'stock_note' => [
                'entity_name' => 'stock note',
                'form'        => $stockNoteForm->createView(),
            ],
        ];
    }
}
