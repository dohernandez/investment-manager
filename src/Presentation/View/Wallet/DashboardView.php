<?php

namespace App\Presentation\View\Wallet;

use App\Presentation\Form\Wallet\CreateOperationType;
use Symfony\Component\Form\FormFactoryInterface;

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
        return [
                'wallet_id' => $id,
                'operation' => $this->getOperationPanel(),
            ] + [
                'page_title' => 'Wallets',
            ] + $context;
    }

    /**
     * @inheritDoc
     */
    protected function getOperationPanel(): array
    {
        $operationForm = $this->form->create(CreateOperationType::class);

        return [
            'entity_name'  => 'operation',
            'search_width' => '235px',
            'form'         => $operationForm->createView(),

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
}
