<?php

namespace App\Twig\Parameters;

use App\Entity\Entity;
use App\Entity\Wallet;
use App\Form\OperationType;
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
                'render' => 'money',
            ],
            [
                'name' => 'capital',
                'render' => 'money',
            ],
            [
                'name' => 'funds',
                'render' => 'money',
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
        $walletOperationForm = $this->form->create(OperationType::class);

        return [
                'wallet' => $wallet,
                'entity_name' => trim(implode(' ', preg_split('/(?=[A-Z])/', $this->getEntityName()))),

                'wallet_operation_search_width' => '235px',
                'wallet_operation_entity_name' => 'operation',
                'wallet_operation_fields' => $this->getOperationFields(),
                'wallet_operation_form' => $walletOperationForm->createView(),

                'wallet_position_entity_name' => 'position',
                'wallet_position_fields' => $this->getPositionFields(),
                'wallet_position_buttons' => [
                    [
                        'type' => 'warning',
                        'jsClass' => 'js-position-dividend',
                        'icon' => 'fas fa-donate',
                    ],
                    [
                        'type' => 'success',
                        'jsClass' => 'js-position-buy',
                        'icon' => 'fas fa-bold',
                    ],
                    [
                        'type' => 'danger',
                        'jsClass' => 'js-position-sell',
                        'icon' => 'fab fa-stripe-s',
                    ],
                ],
                'wallet_position_dividend_entity_name' => 'position_dividend',
                'wallet_position_dividend_fields' => $this->getPositionDividendFields(),
                'wallet_coming_dividend_entity_name' => 'coming_dividend',
                'wallet_coming_dividend_fields' => $this->getComingDividendFields(),
            ] + $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOperationFields(): array
    {
        return [
            [
                'name' => 'dateAt',
                'label' => 'Date',
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name' => 'type',
            ],
            [
                'name' => 'stock.name',
                'label' => 'Stock',
                'render' => 'check',
                'check' => 'stock',
            ],
            [
                'name' => 'stock.symbol',
                'label' => 'Symbol',
                'render' => 'check',
                'check' => 'stock',
            ],
            [
                'name' => 'stock.market.symbol',
                'label' => 'Market',
                'render' => 'check',
                'check' => 'stock',
            ],
            [
                'name' => 'amount',
                'label' => 'Amt',
            ],
            [
                'name' => 'value',
                'render' => 'money',
            ],
            [
                'name' => 'commissions',
                'render' => 'money',
                'col_with' => '120',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getPositionFields(): array
    {
        return [
            [
                'name' => 'stock.name',
                'label' => 'Stock',
                'col_with' => '264',
                'class' => 'js-manager-table-extra-cell-show',
            ],
            [
                'name' => 'stock.symbol',
                'label' => 'Symbol',
            ],
            [
                'name' => 'stock.market.symbol',
                'label' => 'Market',
            ],
            [
                'name' => 'stock.value',
                'label' => 'Price',
                'render' => 'money',
                'class' => 'js-manager-table-extra-cell-hide',
            ],
            [
                'name' => 'amount',
                'label' => 'Amt',
            ],
            [
                'name' => 'capital',
                'render' => 'money',
            ],
            [
                'name' => 'invested',
                'render' => 'money',
            ],
            [
                'name' => 'dividend',
                'render' => 'money',
            ],
            [
                'name' => 'displayBenefits',
                'label' => 'benefits',
                'render' => 'quantity',
                'quantity' => 'benefits.value',
                'class' => 'js-manager-table-extra-cell-hide',
            ],
            [
                'name' => 'displayChange',
                'label' => 'Change',
                'render' => 'quantity',
                'quantity' => 'change.value',
                'class' => 'js-manager-table-extra-cell-hide',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getPositionDividendFields(): array
    {
        return [
            [
                'name' => 'stock.symbol',
                'label' => 'Symbol',
            ],
            [
                'name' => 'stock.market.symbol',
                'label' => 'Market',
            ],
            [
                'name' => 'invested',
                'render' => 'money',
            ],
            [
                'name' => 'amount',
                'label' => 'Amt',
            ],
            [
                'name' => 'stock.displayDividendYield',
                'label' => 'D. Yield',
            ],
            [
                'name' => 'stock.exDate',
                'label' => 'Ex. Date',
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name' => 'displayDividendYield',
                'label' => 'R. D. Yield',
                'class' => 'js-manager-table-extra-cell-hide',
            ],
        ];
    }

    private function getComingDividendFields(): array
    {
        return [
            [
                'name' => 'stock.symbol',
                'label' => 'Symbol',
            ],
            [
                'name' => 'stock.exDate',
                'label' => 'Ex. Date',
                'render' => 'date',
                'date_format' => 'DD/MM/YYYY', // moment date format https://momentjs.com/docs/#/displaying/format/
            ],
            [
                'name' => 'displayDividendYield',
                'label' => 'R. D. Yield',
                'class' => 'js-manager-table-extra-cell-hide',
            ],
        ];
    }
}
