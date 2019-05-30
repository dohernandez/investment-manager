<?php

namespace App\Twig\Parameters;

use App\Entity\Broker;
use App\Form\BrokerType;

/**
 * Represents the transfer parameters used to build index view
 */
class BrokerViewParameters extends AbstractViewParameters
{
    /**
     * {@inheritdoc}
     */
    protected $entityClass = Broker::class;

    /**
     * {@inheritdoc}
     */
    protected $newFormTypeClass = BrokerType::class;

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
                'name' => 'site',
            ],
            [
                'name' => 'account',
                'render' => 'account',
            ],
        ];
    }

    public function index(array $entities = [], array $context = []): array
    {
        return parent::index($entities, $context + [
                'buttons' => [
                    [
                        'type' => 'warning',
                        'jsClass' => 'js-entity-edit-broker-stocks',
                        'icon' => 'fas fa-money-bill',
                    ],
                ],
            ]);
    }
}
