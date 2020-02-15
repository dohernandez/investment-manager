<?php

namespace App\Presentation\View\Wallet;

use App\Domain\Wallet\Wallet;
use App\Presentation\Form\Wallet\CreateWalletType;
use App\Presentation\View\AbstractView;

final class DashboardView
{
    /**
     * @var string
     */
    protected $prefixRoute = 'wallet';

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
                'fields' => $this->getFields(),
            ] + [
                'page_title' => 'Wallets',
            ] + $context;
    }

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
}
