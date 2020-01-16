<?php

namespace App\Presentation\View\Broker;

use App\Domain\Broker\Broker;
use App\Presentation\Form\Broker\CreateBrokerType;
use App\Presentation\View\AbstractView;

final class IndexView extends AbstractView
{
    /**
     * @inheritDoc
     */
    protected $entityClass = Broker::class;

    /**
     * @inheritDoc
     */
    protected $prefixRoute = 'account';

    /**
     * @inheritDoc
     */
    protected $createFormTypeClass = CreateBrokerType::class;

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
                'name' => 'site',
            ],
            [
                'name' => 'currency',
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
